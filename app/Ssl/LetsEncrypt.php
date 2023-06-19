<?php

namespace App\Ssl;

use Illuminate\Support\Facades\Process;

class LetsEncrypt
{
    private string $acme;

    public function __construct()
    {
        $this->acme = base_path("acme/acmephp.phar");
    }

    public function register(string $email): string
    {
        $process = Process::run("php $this->acme register $email");
        return $process->output();
    }

    public function authorize(string $domain)
    {
        $process = Process::run("php $this->acme authorize $domain");
        return $this->extractJSON($process->output());
    }

    private function extractJSON($text)
    {
        $pattern = '/\{.*\}/s'; // Regular expression pattern to match JSON

        // Find the JSON portion using regular expressions
        preg_match($pattern, $text, $matches);
        $jsonString = $matches[0] ?? '';


        return json_decode($jsonString, true);
    }

}
