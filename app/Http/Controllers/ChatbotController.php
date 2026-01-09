<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        try {
            // 1. Fetch Fleet Data (Cached for 5 minutes)
            $fleet = Cache::remember('chatbot_fleet_data', 300, function () {
                return Vehicle::where('availability', true)
                    ->get(['model', 'vehicle_category', 'priceHour', 'fuelType', 'type'])
                    ->map(function ($vehicle) {
                        return [
                            'name' => $vehicle->model,
                            'category' => $vehicle->vehicle_category,
                            'type' => $vehicle->type,
                            'rate' => $vehicle->priceHour . '/hour',
                            'fuel' => $vehicle->fuelType,
                        ];
                    })
                    ->toJson();
            });

            // UPDATED SYSTEM MESSAGE WITH SITE MAP
            $systemMessage = <<<'SYSTEM'
            You are the HASTA Assistant - a professional car rental chatbot for a student car rental service in Malaysia.
            
            1. SYSTEM NAVIGATION MAP (Use this to guide users):
            - To Pay Fines/Debt: Go to the "Payments" page.
            - To Check Payment History: Go to the "Payments" page.
            - To Make a Booking: Click "Book Now".
            - To View Active/Past Rentals: Go to "My Bookings".
            - To Check Loyalty Points/Redeem: Go to the "Loyalty" page.
            - To Update Profile/License: Go to "Profile" settings.

            2. Fleet Information (Current Available Vehicles):
            SYSTEM . $fleet . <<<'SYSTEM'

            Your responsibilities:
            1. Help users navigate the system (e.g., "How do I pay a fine?" -> "You can pay fines on the Payments page.")
            2. Recommend suitable vehicles from the fleet
            3. Provide pricing information directly from the list
            4. Explain rental terms (3-hour cooldown, 30-min payment window)
            5. Be helpful, professional, and concise

            Guidelines:
            - If a user asks "how to" do something in the app, tell them exactly which page to visit.
            - Keep responses under 3 sentences unless more detail is needed.
            - Be friendly and encouraging.
            - Never share sensitive company information (like database IDs).
            SYSTEM;

            // 2. Use direct HTTP client for OpenRouter
            $apiKey = config('openai.api_key') ?? env('OPENAI_API_KEY');
            
            if (!$apiKey) {
                throw new \Exception('OpenAI API Key is not configured');
            }

            $httpClient = new \GuzzleHttp\Client();
            
            $response = $httpClient->post('https://openrouter.ai/api/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => 'HASTA Car Rental',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'deepseek/deepseek-chat',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemMessage],
                        ['role' => 'user', 'content' => $request->message],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 500,
                ],
                'timeout' => 90,
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (isset($data['choices'][0]['message']['content'])) {
                $reply = $data['choices'][0]['message']['content'];
            } else {
                $reply = 'I couldn\'t understand that. Could you rephrase your question?';
            }

            return response()->json([
                'reply' => $reply
            ]);

        } catch (\Exception $e) {
            Log::error('Chatbot API Error', [
                'user_id' => auth()->id(),
                'message' => $request->message,
                'error' => $e->getMessage(),
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'reply' => 'Sorry, I\'m experiencing technical difficulties. Please try again or contact our support team at +60 11-1090 0700.'
            ], 500);
        }
    }
}