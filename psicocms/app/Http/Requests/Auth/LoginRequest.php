<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\Phone;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public const MAX_ATTEMPTS = 5;

    private const DUMMY_HASH = '$2y$12$IVdByjpF0X64hzNtd7j8huXYFt/hMM9IfVSfFrF9Ey4Nd2d.YCYsi';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'phone' => Phone::normalize($this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function authenticate(): User
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->input('email'))->first();
        $passwordMatches = Hash::check((string) $this->input('password'), $user?->password ?? self::DUMMY_HASH);
        $phoneMatches = $user !== null && hash_equals((string) $user->phone, (string) $this->input('phone'));

        if (! $user || ! $phoneMatches || ! $passwordMatches) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages(['login' => __('auth.failed')]);
        }

        if (Hash::needsRehash($user->password)) {
            $user->forceFill(['password' => Hash::make((string) $this->input('password'))])->save();
        }

        RateLimiter::clear($this->throttleKey());
        Auth::login($user, $this->boolean('remember'));

        return $user;
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        throw ValidationException::withMessages([
            'login' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($this->throttleKey())]),
        ]);
    }

    private function throttleKey(): string
    {
        return Str::transliterate($this->input('email').'|'.$this->ip());
    }
}
