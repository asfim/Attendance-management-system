<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Donation;
use App\Models\DonationCampaign;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        $query = Donation::with('campaign')->orderBy('donation_date', 'desc');

        if ($request->has('campaign_id') && $request->campaign_id != '') {
            $query->where('campaign_id', $request->campaign_id);
        }

        $donations = $query->paginate(20);
        $campaigns = DonationCampaign::where('status', 'active')->orderBy('name')->get();

        $totalDonations = Donation::sum('amount');

        return view('admin.finance.donations.index', compact('donations', 'campaigns', 'totalDonations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'donor_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'donation_date' => 'required|date',
            'type' => 'required|string',
            'campaign_id' => 'nullable|exists:donation_campaigns,id',
            'receipt_path' => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
        ]);

        $data = $request->all();

        if ($request->hasFile('receipt_path')) {
            $data['receipt_path'] = $request->file('receipt_path')->store('finance/donations', 'public');
        }

        Donation::create($data);

        return back()->with('success', 'Donation recorded successfully!');
    }

    public function destroy($id)
    {
        $donation = Donation::findOrFail($id);
        if ($donation->receipt_path && \Storage::disk('public')->exists($donation->receipt_path)) {
            \Storage::disk('public')->delete($donation->receipt_path);
        }
        $donation->delete();
        return back()->with('success', 'Donation deleted successfully!');
    }

    // Campaign Methods
    public function campaigns()
    {
        $campaigns = DonationCampaign::withCount('donations')->get();
        return view('admin.finance.donations.campaigns', compact('campaigns'));
    }

    public function storeCampaign(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'goal_amount' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        DonationCampaign::create($request->all());
        return back()->with('success', 'Donation Campaign created successfully!');
    }

    public function updateCampaign(Request $request, $id)
    {
        $campaign = DonationCampaign::findOrFail($id);
        $request->validate([
            'status' => 'required|in:active,completed,cancelled',
        ]);
        $campaign->update(['status' => $request->status]);
        return back()->with('success', 'Campaign status updated!');
    }

    public function destroyCampaign($id)
    {
        $campaign = DonationCampaign::findOrFail($id);
        if ($campaign->donations()->count() > 0) {
            return back()->with('error', 'Cannot delete campaign with existing donations.');
        }
        $campaign->delete();
        return back()->with('success', 'Campaign deleted successfully!');
    }
}
