<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class LegalPageController extends Controller
{
    /**
     * Display Privacy Policy Page
     */
    public function privacy()
    {
        return view('player.legal.privacy');
    }

    /**
     * Display Terms & Conditions Page
     */
    public function terms()
    {
        return view('player.legal.terms');
    }

    /**
     * Display Responsible Gaming Policy Page
     */
    public function responsibleGaming()
    {
        return view('player.legal.responsible_gaming');
    }

    /**
     * Display Contact Us & Support Page
     */
    public function contact()
    {
        return view('player.legal.contact');
    }

    /**
     * Handle Customer Contact Inquiry Form Submission
     */
    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:150',
            'mobile'   => 'nullable|string|max:20',
            'subject'  => 'required|string|max:150',
            'message'  => 'required|string|min:10|max:2000',
        ]);

        try {
            Mail::html("
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 10px;'>
                    <h3 style='color: #1E88E5;'>New GameHub Support Inquiry</h3>
                    <p><strong>From:</strong> {$validated['name']} ({$validated['email']})</p>
                    <p><strong>Mobile:</strong> " . ($validated['mobile'] ?? 'N/A') . "</p>
                    <p><strong>Subject:</strong> {$validated['subject']}</p>
                    <hr style='border: none; border-top: 1px solid #e5e7eb;' />
                    <p><strong>Message:</strong></p>
                    <p style='white-space: pre-wrap; background: #f9fafb; padding: 15px; border-radius: 8px;'>{$validated['message']}</p>
                </div>
            ", function ($message) use ($validated) {
                $message->to('rivexagames@gmail.com')
                        ->replyTo($validated['email'], $validated['name'])
                        ->subject('GameHub Support Ticket: ' . $validated['subject']);
            });
        } catch (\Throwable $e) {
            logger()->error("Contact Mail Error: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Thank you! Your message has been received. Our support team will get back to you within 24 hours.');
    }

    /**
     * Display Legal Availability & State Restrictions Page
     */
    public function legalAvailability()
    {
        return view('player.legal.legal_availability');
    }

    /**
     * Display HTTPS Security & Data Safeguards Page
     */
    public function security()
    {
        return view('player.legal.security');
    }
}
