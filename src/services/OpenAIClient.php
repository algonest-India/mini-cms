<?php
/**
 * OpenAI Client Service
 *
 * This class provides integration with OpenAI's API for generating blog post content.
 * It uses the Chat Completions endpoint with GPT-4o-mini model to create engaging blog posts
 * based on provided titles. The client handles authentication, SSL verification, and error handling.
 *
 * Environment Variables Required:
 * - OPENAI_API_KEY: Your OpenAI API key
 *
 * Features:
 * - Automatic SSL certificate verification handling for different environments
 * - Configurable timeout for API requests
 * - Structured prompts for consistent content generation
 */

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class OpenAIClient
{
    private Client $client;
    private string $apiKey;
    private string|bool $verify;

    /**
     * Constructor - Initializes the HTTP client with OpenAI API configuration.
     * Loads API key from environment variables and sets up SSL verification.
     */
    public function __construct()
    {
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
        $this->verify = $this->resolveVerifyPath();
        $this->client = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'timeout' => 15.0, // 15 second timeout for API calls
            'verify' => $this->verify, // SSL certificate verification
        ]);
    }

    /**
     * Generate content using OpenAI Chat Completions API.
     * Creates a 200-word blog post based on the provided title.
     *
     * @param string $title The topic/title for the blog post
     * @return string The generated blog post content
     * @throws \RuntimeException on missing API key or HTTP failure
     */
    public function generateContent(string $title): string
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('OpenAI API key is not configured.');
        }

        // Create a structured prompt for consistent blog post generation
        $prompt = "Generate engaging 200-word blog post on {$title}";

        try {
            $response = $this->client->post('chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o-mini', // Using cost-effective GPT-4o-mini model
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to contact OpenAI: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }

        $data = json_decode((string) $response->getBody(), true);
        $content = $data['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            throw new \RuntimeException('OpenAI returned no content.');
        }

        return trim($content);
    }

    /**
     * Resolve SSL verification path/flag.
     *
     * Priority:
     * 1. OPENAI_CA_BUNDLE env var (path to CA certificate bundle)
     * 2. OPENAI_DISABLE_SSL_VERIFY=true for development (Windows SSL issues)
     * 3. Default to true (use system CA bundle)
     */
    private function resolveVerifyPath(): string|bool
    {
        // Option 1: Custom CA bundle path from environment variable
        $bundle = $_ENV['OPENAI_CA_BUNDLE'] ?? '';
        if ($bundle && file_exists($bundle)) {
            return $bundle;
        }

        // Option 2: Disable SSL verification for development environments
        // Useful for Windows systems with SSL certificate issues
        $disableVerify = $_ENV['OPENAI_DISABLE_SSL_VERIFY'] ?? '';
        if (strtolower($disableVerify) === 'true' || $disableVerify === '1') {
            // Log a warning in development (but don't throw)
            error_log('WARNING: SSL verification is disabled. This should only be used in development!');
            return false;
        }

        // Option 3: Default - use system CA bundle for secure connections
        return true;
    }
}
}




