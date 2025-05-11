<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TranslationController extends Controller
{
    /**
     * Display a listing of the translations.
     */
    public function index(Request $request): JsonResponse
    {
        // Start time measurement
        $startTime = microtime(true);
        
        // Use query builder for better performance
        $query = DB::table('translations');

        // Apply filters if provided
        if ($request->has('key')) {
            $query->where('key', 'like', "%{$request->key}%");
        }

        if ($request->has('locale')) {
            $query->where('locale', $request->locale);
        }

        if ($request->has('content')) {
            $query->where('content', 'like', "%{$request->content}%");
        }

        if ($request->has('tags')) {
            $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            
            $query->where(function ($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhereJsonContains('tags', $tag);
                }
            });
        }

        // Only select needed columns for better performance
        $query->select(['id', 'key', 'locale', 'content', 'tags']);
        
        // Use chunked pagination for large datasets
        $perPage = $request->per_page ?? 15;
        $translations = $query->paginate($perPage);

        // Calculate response time
        $responseTime = (microtime(true) - $startTime) * 1000;
        
        return response()->json([
            'data' => $translations->items(),
            'pagination' => [
                'total' => $translations->total(),
                'per_page' => $translations->perPage(),
                'current_page' => $translations->currentPage(),
                'last_page' => $translations->lastPage(),
            ],
            'response_time_ms' => round($responseTime, 2)
        ]);
    }

    /**
     * Store a newly created translation in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:255',
            'locale' => 'required|string|max:5',
            'content' => 'required|string',
            'tags' => 'sometimes|array',
            'tags.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if translation with the same key and locale already exists
        $existingTranslation = DB::table('translations')
            ->where('key', $request->key)
            ->where('locale', $request->locale)
            ->first();

        if ($existingTranslation) {
            return response()->json([
                'message' => 'Translation with this key and locale already exists',
                'translation' => $existingTranslation
            ], 409);
        }

        // Create a new translation
        $id = DB::table('translations')->insertGetId([
            'key' => $request->key,
            'locale' => $request->locale,
            'content' => $request->content,
            'tags' => json_encode($request->tags ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $translation = DB::table('translations')->where('id', $id)->first();
        
        // Clear cache when a translation is added
        $this->clearTranslationCache($request->locale);

        $responseTime = (microtime(true) - $startTime) * 1000;
        
        return response()->json([
            'message' => 'Translation created successfully',
            'translation' => $translation,
            'response_time_ms' => round($responseTime, 2)
        ], 201);
    }

    /**
     * Display the specified translation.
     */
    public function show(string $id): JsonResponse
    {
        $startTime = microtime(true);
        
        $translation = DB::table('translations')->where('id', $id)->first();
        
        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }
        
        $responseTime = (microtime(true) - $startTime) * 1000;
        
        return response()->json([
            'translation' => $translation,
            'response_time_ms' => round($responseTime, 2)
        ]);
    }

    /**
     * Update the specified translation in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $startTime = microtime(true);
        
        $validator = Validator::make($request->all(), [
            'key' => 'sometimes|string|max:255',
            'locale' => 'sometimes|string|max:5',
            'content' => 'sometimes|string',
            'tags' => 'sometimes|array',
            'tags.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $translation = DB::table('translations')->where('id', $id)->first();

        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }

        // If key or locale is being changed, check for uniqueness
        if (($request->has('key') && $translation->key !== $request->key) || 
            ($request->has('locale') && $translation->locale !== $request->locale)) {
            
            $existingTranslation = DB::table('translations')
                ->where('key', $request->key ?? $translation->key)
                ->where('locale', $request->locale ?? $translation->locale)
                ->where('id', '!=', $id)
                ->first();

            if ($existingTranslation) {
                return response()->json([
                    'message' => 'Translation with this key and locale already exists',
                    'translation' => $existingTranslation
                ], 409);
            }
        }

        // Prepare update data
        $updateData = [];
        
        if ($request->has('key')) {
            $updateData['key'] = $request->key;
        }
        
        if ($request->has('locale')) {
            $updateData['locale'] = $request->locale;
        }
        
        if ($request->has('content')) {
            $updateData['content'] = $request->content;
        }
        
        if ($request->has('tags')) {
            $updateData['tags'] = json_encode($request->tags);
        }
        
        $updateData['updated_at'] = now();
        
        // Update the translation
        DB::table('translations')->where('id', $id)->update($updateData);
        
        // Retrieve the updated translation
        $updatedTranslation = DB::table('translations')->where('id', $id)->first();
        
        // Clear cache when a translation is updated
        $this->clearTranslationCache($updatedTranslation->locale);
        
        if ($request->has('locale') && $translation->locale != $request->locale) {
            $this->clearTranslationCache($translation->locale);
        }

        $responseTime = (microtime(true) - $startTime) * 1000;
        
        return response()->json([
            'message' => 'Translation updated successfully',
            'translation' => $updatedTranslation,
            'response_time_ms' => round($responseTime, 2)
        ]);
    }

    /**
     * Remove the specified translation from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $startTime = microtime(true);
        
        $translation = DB::table('translations')->where('id', $id)->first();
        
        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }
        
        // Delete the translation
        DB::table('translations')->where('id', $id)->delete();
        
        // Clear cache when a translation is deleted
        $this->clearTranslationCache($translation->locale);

        $responseTime = (microtime(true) - $startTime) * 1000;
        
        return response()->json([
            'message' => 'Translation deleted successfully',
            'response_time_ms' => round($responseTime, 2)
        ], 200);
    }

    /**
     * Export translations as JSON for frontend applications.
     * Optimized for large datasets with response time < 500ms.
     */
    public function export(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        
        $locale = $request->locale ?? 'en';
        $tags = $request->has('tags') ? 
            (is_array($request->tags) ? $request->tags : explode(',', $request->tags)) : 
            null;

        // Cache key based on request parameters
        $cacheKey = "translations_{$locale}_" . ($tags ? implode('_', $tags) : 'all');

        // Get from cache or fetch from database
        $translations = Cache::remember($cacheKey, 3600, function () use ($locale, $tags) {
            // Use query builder for better performance
            $query = DB::table('translations')
                ->where('locale', $locale)
                ->select(['key', 'content']);
            
            if ($tags) {
                $query->where(function ($q) use ($tags) {
                    foreach ($tags as $tag) {
                        $q->orWhereJsonContains('tags', $tag);
                    }
                });
            }
            
            // Process in chunks for large datasets
            $result = [];
            $query->orderBy('key')->chunk(1000, function ($translations) use (&$result) {
                foreach ($translations as $translation) {
                    $result[$translation->key] = $translation->content;
                }
            });
            
            return $result;
        });

        $responseTime = (microtime(true) - $startTime) * 1000;
        
        // Add response time as a header for monitoring
        return response()->json($translations)
            ->header('X-Response-Time-Ms', round($responseTime, 2));
    }

    /**
     * Search translations by various criteria.
     */
    public function search(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        
        $validator = Validator::make($request->all(), [
            'query' => 'required|string',
            'locale' => 'sometimes|string|max:5',
            'tags' => 'sometimes|array',
            'tags.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Use query builder for better performance
        $query = DB::table('translations');

        // Apply locale filter if provided
        if ($request->has('locale')) {
            $query->where('locale', $request->locale);
        }

        // Apply tags filter if provided
        if ($request->has('tags')) {
            $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            $query->where(function ($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhereJsonContains('tags', $tag);
                }
            });
        }

        // Search in key and content
        $searchQuery = $request->query('query');
        $query->where(function ($q) use ($searchQuery) {
            $q->where('key', 'like', "%{$searchQuery}%")
              ->orWhere('content', 'like', "%{$searchQuery}%");
        });

        // Only select needed columns for better performance
        $query->select(['id', 'key', 'locale', 'content', 'tags']);
        
        // Use chunked pagination for large datasets
        $perPage = $request->per_page ?? 15; 
        $translations = $query->paginate($perPage);
        
        $responseTime = (microtime(true) - $startTime) * 1000;
        
        return response()->json([
            'data' => $translations->items(),
            'pagination' => [
                'total' => $translations->total(),
                'per_page' => $translations->perPage(),
                'current_page' => $translations->currentPage(),
                'last_page' => $translations->lastPage(),
            ],
            'response_time_ms' => round($responseTime, 2)
        ]);
    }

    /**
     * Clear translation cache for a specific locale.
     * More targeted than flushing the entire cache.
     */
    private function clearTranslationCache(?string $locale = null): void
    {
        if ($locale) {
            // Pattern for cache keys related to this locale
            $pattern = "translations_{$locale}_*";
            
            // Get cache keys matching pattern and delete them
            foreach (Cache::getStore()->many([$pattern]) as $key => $value) {
                Cache::forget($key);
            }
        } else {
            // If no locale specified, clear all translation caches
            $pattern = "translations_*";
            
            // Get cache keys matching pattern and delete them
            foreach (Cache::getStore()->many([$pattern]) as $key => $value) {
                Cache::forget($key);
            }
        }
    }
} 