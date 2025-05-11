<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // English translations
        Translation::create([
            'key' => 'welcome_message',
            'locale' => 'en',
            'content' => 'Welcome!',
            'tags' => ['web', 'mobile'],
        ]);

        Translation::create([
            'key' => 'goodbye_message',
            'locale' => 'en',
            'content' => 'Goodbye!',
            'tags' => ['web', 'mobile'],
        ]);

        Translation::create([
            'key' => 'login_button',
            'locale' => 'en',
            'content' => 'Login',
            'tags' => ['web'],
        ]);

        Translation::create([
            'key' => 'signup_button',
            'locale' => 'en',
            'content' => 'Sign Up',
            'tags' => ['web', 'desktop'],
        ]);

        // French translations
        Translation::create([
            'key' => 'welcome_message',
            'locale' => 'fr',
            'content' => 'Bienvenue!',
            'tags' => ['web', 'mobile'],
        ]);

        Translation::create([
            'key' => 'goodbye_message',
            'locale' => 'fr',
            'content' => 'Au revoir!',
            'tags' => ['web', 'mobile'],
        ]);

        Translation::create([
            'key' => 'login_button',
            'locale' => 'fr',
            'content' => 'Connexion',
            'tags' => ['web'],
        ]);

        Translation::create([
            'key' => 'signup_button',
            'locale' => 'fr',
            'content' => 'S\'inscrire',
            'tags' => ['web', 'desktop'],
        ]);

        // Spanish translations
        Translation::create([
            'key' => 'welcome_message',
            'locale' => 'es',
            'content' => '¡Bienvenido!',
            'tags' => ['web', 'mobile'],
        ]);

        Translation::create([
            'key' => 'goodbye_message',
            'locale' => 'es',
            'content' => '¡Adiós!',
            'tags' => ['web', 'mobile'],
        ]);

        Translation::create([
            'key' => 'login_button',
            'locale' => 'es',
            'content' => 'Iniciar sesión',
            'tags' => ['web'],
        ]);

        Translation::create([
            'key' => 'signup_button',
            'locale' => 'es',
            'content' => 'Registrarse',
            'tags' => ['web', 'desktop'],
        ]);
    }
} 