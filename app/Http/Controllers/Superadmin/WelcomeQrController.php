<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * The single, general "lobby QR" — unlike a Space's own QR (tied to one
 * table, generated per-row in SpaceController), this points at the
 * Welcome flow's fixed URL, so there's exactly one to print, not one per
 * table. Same Endroid QR generation approach as SpaceController::qrCode().
 */
class WelcomeQrController extends Controller
{
    public function print(): View
    {
        return view('superadmin.welcome-qr.print', [
            'resortName' => Setting::current()->resort_name,
        ]);
    }

    public function image(Request $request): Response
    {
        $result = (new Builder(
            writer: new SvgWriter(),
            data: route('customer.welcome.show'),
            size: 300,
            margin: 10,
        ))->build();

        $headers = ['Content-Type' => $result->getMimeType()];

        if ($request->boolean('download')) {
            $headers['Content-Disposition'] = 'attachment; filename="welcome-qr.svg"';
        }

        return response($result->getString(), 200, $headers);
    }
}
