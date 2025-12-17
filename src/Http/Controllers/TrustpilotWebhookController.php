<?php

declare(strict_types=1);

namespace Mralston\Trustpilot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;
use Mralston\Trustpilot\Events\ReviewCreated;
use Mralston\Trustpilot\Events\ReviewUpdated;
use Mralston\Trustpilot\Events\ReviewDeleted;
use Mralston\Trustpilot\Events\InvitationCreated;
use Mralston\Trustpilot\Events\InvitationSent;
use Mralston\Trustpilot\Events\InvitationFailed;

class TrustpilotWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $secret = (string) config('trustpilot.webhook.secret');
        $headerName = (string) config('trustpilot.webhook.header', 'X-Trustpilot-Secret');

        if ($secret !== '' && $request->header($headerName) !== $secret) {
            return response('Invalid signature', 403);
        }

        $payload = $request->json()->all();
        $eventType = $payload['event'] ?? $payload['type'] ?? null;

        // Dispatch Laravel events based on generic event type
        switch ($eventType) {
            case 'review.created':
            case 'trustpilot.review.created':
                Event::dispatch(new ReviewCreated($payload));
                break;
            case 'review.updated':
            case 'trustpilot.review.updated':
                Event::dispatch(new ReviewUpdated($payload));
                break;
            case 'review.deleted':
            case 'trustpilot.review.deleted':
                Event::dispatch(new ReviewDeleted($payload));
                break;
            case 'invitation.created':
            case 'trustpilot.invitation.created':
                Event::dispatch(new InvitationCreated($payload));
                break;
            case 'invitation.sent':
            case 'trustpilot.invitation.sent':
                Event::dispatch(new InvitationSent($payload));
                break;
            case 'invitation.failed':
            case 'trustpilot.invitation.failed':
                Event::dispatch(new InvitationFailed($payload));
                break;
            default:
                // Unknown event; still return OK to prevent retries
                break;
        }

        return response('OK', 200);
    }
}
