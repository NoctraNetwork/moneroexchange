<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'user.protect']);
    }

    /**
     * Show feedback form for a completed trade
     */
    public function create(Trade $trade)
    {
        $user = Auth::user();
        
        // Check if user is part of this trade
        if ($trade->buyer_id !== $user->id && $trade->seller_id !== $user->id) {
            abort(403, 'Access denied');
        }

        // Check if trade is completed
        if (!$trade->isCompleted()) {
            return redirect()->back()->with('error', 'Feedback can only be left for completed trades');
        }

        // Check if user has already left feedback
        $existingFeedback = Feedback::where('trade_id', $trade->id)
            ->where('from_user_id', $user->id)
            ->first();

        if ($existingFeedback) {
            return redirect()->back()->with('error', 'You have already left feedback for this trade');
        }

        // Determine who to give feedback to
        $feedbackTo = $trade->buyer_id === $user->id ? $trade->seller : $trade->buyer;
        
        return view('feedback.create', compact('trade', 'feedbackTo'));
    }

    /**
     * Store feedback
     */
    public function store(Request $request, Trade $trade)
    {
        $user = Auth::user();
        
        // Validate request
        $request->validate([
            'rating' => 'required|in:+1,0,-1',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if user is part of this trade
        if ($trade->buyer_id !== $user->id && $trade->seller_id !== $user->id) {
            abort(403, 'Access denied');
        }

        // Check if trade is completed
        if (!$trade->isCompleted()) {
            return redirect()->back()->with('error', 'Feedback can only be left for completed trades');
        }

        // Check if user has already left feedback
        $existingFeedback = Feedback::where('trade_id', $trade->id)
            ->where('from_user_id', $user->id)
            ->first();

        if ($existingFeedback) {
            return redirect()->back()->with('error', 'You have already left feedback for this trade');
        }

        try {
            DB::beginTransaction();

            // Determine who to give feedback to
            $feedbackToId = $trade->buyer_id === $user->id ? $trade->seller_id : $trade->buyer_id;

            // Create feedback
            $feedback = Feedback::create([
                'trade_id' => $trade->id,
                'from_user_id' => $user->id,
                'to_user_id' => $feedbackToId,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            // Add trade event
            $trade->addEvent('feedback_left', $user->id, [
                'feedback_id' => $feedback->id,
                'rating' => $request->rating,
                'to_user_id' => $feedbackToId
            ]);

            DB::commit();

            return redirect()->route('trades.show', $trade)
                ->with('success', 'Feedback submitted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create feedback', [
                'trade_id' => $trade->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to submit feedback. Please try again.');
        }
    }

    /**
     * Show user's feedback history
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $feedback = Feedback::where('to_user_id', $user->id)
            ->with(['fromUser', 'trade'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculate feedback statistics
        $stats = [
            'total_feedback' => $feedback->total(),
            'positive_count' => Feedback::where('to_user_id', $user->id)->positive()->count(),
            'neutral_count' => Feedback::where('to_user_id', $user->id)->neutral()->count(),
            'negative_count' => Feedback::where('to_user_id', $user->id)->negative()->count(),
            'reputation_score' => $user->getReputationScore(),
        ];

        return view('feedback.index', compact('feedback', 'stats'));
    }

    /**
     * Show feedback given by user
     */
    public function given(Request $request)
    {
        $user = Auth::user();
        
        $feedback = Feedback::where('from_user_id', $user->id)
            ->with(['toUser', 'trade'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('feedback.given', compact('feedback'));
    }

    /**
     * Show user's public profile with feedback
     */
    public function profile(User $user)
    {
        // Get user's feedback statistics
        $stats = [
            'total_feedback' => Feedback::where('to_user_id', $user->id)->count(),
            'positive_count' => Feedback::where('to_user_id', $user->id)->positive()->count(),
            'neutral_count' => Feedback::where('to_user_id', $user->id)->neutral()->count(),
            'negative_count' => Feedback::where('to_user_id', $user->id)->negative()->count(),
            'reputation_score' => $user->getReputationScore(),
            'completion_rate' => $user->getCompletionRate(),
            'account_age_days' => $user->getAccountAgeDays(),
        ];

        // Get recent feedback
        $recentFeedback = Feedback::where('to_user_id', $user->id)
            ->with(['fromUser', 'trade'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get trading statistics
        $tradingStats = [
            'total_trades' => Trade::byUser($user->id)->count(),
            'completed_trades' => Trade::byUser($user->id)->completed()->count(),
            'total_volume_xmr' => Trade::byUser($user->id)->completed()->sum('amount_atomic') / 1e12,
        ];

        return view('feedback.profile', compact('user', 'stats', 'recentFeedback', 'tradingStats'));
    }

    /**
     * Get feedback statistics for API
     */
    public function statistics(User $user)
    {
        $stats = [
            'total_feedback' => Feedback::where('to_user_id', $user->id)->count(),
            'positive_count' => Feedback::where('to_user_id', $user->id)->positive()->count(),
            'neutral_count' => Feedback::where('to_user_id', $user->id)->neutral()->count(),
            'negative_count' => Feedback::where('to_user_id', $user->id)->negative()->count(),
            'reputation_score' => $user->getReputationScore(),
            'completion_rate' => $user->getCompletionRate(),
            'account_age_days' => $user->getAccountAgeDays(),
        ];

        return response()->json($stats);
    }
}
