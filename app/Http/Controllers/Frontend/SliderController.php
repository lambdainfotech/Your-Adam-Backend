<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\JsonResponse;

class SliderController extends Controller
{
    /**
     * Get active sliders for frontend display
     */
    public function index(): JsonResponse
    {
        $sliders = Slider::active()
            ->ordered()
            ->get()
            ->map(function ($slider) {
                return [
                    'id' => $slider->id,
                    'title' => $slider->title,
                    'subtitle' => $slider->subtitle,
                    'title_color' => $slider->title_color,
                    'subtitle_color' => $slider->subtitle_color,
                    'title_style' => $slider->title_style,
                    'subtitle_style' => $slider->subtitle_style,
                    'image_url' => $slider->banner_image_url,
                    'button' => $slider->has_button ? [
                        'text' => $slider->button_text,
                        'url' => $slider->button_url,
                        'text_color' => $slider->button_text_color,
                        'background_color' => $slider->button_bg_color,
                        'style' => $slider->button_style,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sliders,
        ]);
    }
}
