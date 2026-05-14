<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    use ApiResponse;

    /**
     * Subscribe to newsletter
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:100|unique:newsletter_subscribers,email',
        ], [
            'email.unique' => 'You are already subscribed to our newsletter.',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors()->toArray());
        }

        $subscriber = NewsletterSubscriber::create([
            'email' => $request->input('email'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->success([
            'id' => $subscriber->id,
            'email' => $subscriber->email,
            'message' => 'Thank you for subscribing to our newsletter!',
        ], 'Newsletter subscription successful');
    }
}
