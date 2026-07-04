<?php

namespace App\Services;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class R2Service
{
    /** @var S3Client */
    protected $client;

    /** @var string */
    protected $bucket;

    public function __construct()
    {
        $this->bucket = env('R2_BUCKET');
        $this->client = new S3Client([
            'region' => env('AWS_DEFAULT_REGION', 'auto'),
            'version' => 'latest',
            'endpoint' => env('R2_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => env('R2_ACCESS_KEY_ID'),
                'secret' => env('R2_SECRET_ACCESS_KEY'),
            ],
        ]);
    }

    /**
     * Upload a file to R2.
     *
     * @param string $key   Object key (path within bucket)
     * @param mixed  $body  File contents (string, resource, stream)
     * @param array  $options Additional S3 put options (e.g., Content-Type)
     * @return array|null Metadata on success, null on failure
     */
    public function upload(string $key, $body, array $options = []): ?array
    {
        try {
            $result = $this->client->putObject(array_merge([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => $body,
            ], $options));

            return [
                'url' => $result->get('ObjectURL'),
                'etag' => $result->get('ETag'),
                'size' => $result->get('ContentLength'),
                'key' => $key,
            ];
        } catch (AwsException $e) {
            logger()->error('R2 upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete an object from R2.
     */
    public function delete(string $key): bool
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
            return true;
        } catch (AwsException $e) {
            logger()->error('R2 delete failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a presigned URL for GET or PUT.
     */
    public function presignUrl(string $key, string $method = 'GET', int $expires = 3600): ?string
    {
        try {
            $command = $this->client->getCommand($method === 'PUT' ? 'putObject' : 'getObject', [
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
            $request = $this->client->createPresignedRequest($command, '+' . $expires . ' seconds');
            return (string) $request->getUri();
        } catch (AwsException $e) {
            logger()->error('R2 presign failed: ' . $e->getMessage());
            return null;
        }
    }
}

?>
