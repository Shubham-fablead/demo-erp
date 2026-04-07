<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\FacebookAppConfiguration;
use App\Models\User;
use App\Models\WhatsAppMessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookAppConfigurationController extends Controller
{
    // Fetch all Facebook App Configurations
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role;
        $userBranchId = $user->id;
        $BranchId = $user->branch_id ?? $userBranchId;

        $selectedSubAdminId = $request->query('selectedSubAdminId');
        $branchIdFilter = $request->query('branch_id');

        $query = FacebookAppConfiguration::with('branch')->where('isDeleted', 0);

        if ($role === 'sub-admin') {
            // Sub-admin: show only their own branch's data
            $query->where('branch_id', $userBranchId);
        } elseif ($role === 'admin' && !empty($selectedSubAdminId)) {
            // Admin with filter: show selected sub-admin's branch data
            $subAdmin = User::find($selectedSubAdminId);
            if ($subAdmin) {
                $query->where('branch_id', $subAdmin->id);
            }
        } elseif ($role === 'staff') {
            $query->where('branch_id', $BranchId);
        }

        // Additional branch filter from dropdown
        if ($branchIdFilter) {
            $query->where('branch_id', $branchIdFilter);
        }

        // Fetch configurations for the selected branch
        $configurations = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => true,
            'data' => $configurations
        ], 200);
    }

    // Store a new Facebook App Configuration
    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role;
        $userBranchId = $user->id;

        // Determine branch_id: use selectedSubAdminId if provided, otherwise use branch_id, or current user ID for Main Branch
        $selectedSubAdminId = $request->selectedSubAdminId;
        $branchId = null;

        if (!empty($selectedSubAdminId) && $selectedSubAdminId !== 'null') {
            $branchId = $selectedSubAdminId;
        } elseif (!empty($request->branch_id)) {
            $branchId = $request->branch_id;
        } else {
            // Main Branch - use current user's ID
            $branchId = $userBranchId;
        }

        $validator = Validator::make($request->all(), [
            'branch_id' => 'nullable|exists:users,id',
            'facebook_app_id' => 'required|string|max:255',
            'facebook_app_secret' => 'required|string',
            'phone_number_id' => 'required|string|max:255',
            'whatsapp_business_account_id' => 'required|string|max:255',
            'access_token' => 'required|string',
            'webhook_url' => 'required|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if configuration already exists for this branch (only for new records)
        if (!$request->has('id') || empty($request->id)) {
            $existingConfig = FacebookAppConfiguration::where('branch_id', $branchId)
                ->where('isDeleted', 0)
                ->first();

            if ($existingConfig) {
                return response()->json([
                    'status' => false,
                    'message' => 'Configuration already exists for this branch. Please update the existing one.'
                ], 422);
            }
        }

        $configuration = FacebookAppConfiguration::create([
            'branch_id' => $branchId,
            'facebook_app_id' => $request->facebook_app_id,
            'facebook_app_secret' => $request->facebook_app_secret,
            'phone_number_id' => $request->phone_number_id,
            'whatsapp_business_account_id' => $request->whatsapp_business_account_id,
            'access_token' => $request->access_token,
            'webhook_url' => $request->webhook_url,
            'isDeleted' => 0
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Facebook App Configuration created successfully!',
            'data' => $configuration->load('branch')
        ], 201);
    }

    // Fetch a specific configuration for editing
    public function show($id)
    {
        $configuration = FacebookAppConfiguration::with('branch')
            ->where('id', $id)
            ->where('isDeleted', 0)
            ->first();

        if (!$configuration) {
            return response()->json([
                'status' => false,
                'message' => 'Configuration not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $configuration
        ], 200);
    }

    // Update Facebook App Configuration
    public function update(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        $configuration = FacebookAppConfiguration::where('id', $id)
            ->where('isDeleted', 0)
            ->first();

        if (!$configuration) {
            return response()->json([
                'status' => false,
                'message' => 'Configuration not found'
            ], 404);
        }

        // Determine branch_id: use selectedSubAdminId if provided, otherwise use branch_id, or current user ID for Main Branch
        $selectedSubAdminId = $request->selectedSubAdminId;
        $branchId = null;

        if (!empty($selectedSubAdminId) && $selectedSubAdminId !== 'null') {
            $branchId = $selectedSubAdminId;
        } elseif (!empty($request->branch_id)) {
            $branchId = $request->branch_id;
        } else {
            // Main Branch - use current user's ID
            $branchId = $user->id;
        }

        $validator = Validator::make($request->all(), [
            'branch_id' => 'nullable|exists:users,id',
            'facebook_app_id' => 'required|string|max:255',
            'facebook_app_secret' => 'required|string',
            'phone_number_id' => 'required|string|max:255',
            'whatsapp_business_account_id' => 'required|string|max:255',
            'access_token' => 'required|string',
            'webhook_url' => 'required|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if another configuration exists for this branch (excluding current)
        $existingConfig = FacebookAppConfiguration::where('branch_id', $branchId)
            ->where('id', '!=', $id)
            ->where('isDeleted', 0)
            ->first();

        if ($existingConfig) {
            return response()->json([
                'status' => false,
                'message' => 'Configuration already exists for this branch.'
            ], 422);
        }

        $configuration->update([
            'branch_id' => $branchId,
            'facebook_app_id' => $request->facebook_app_id,
            'facebook_app_secret' => $request->facebook_app_secret,
            'phone_number_id' => $request->phone_number_id,
            'whatsapp_business_account_id' => $request->whatsapp_business_account_id,
            'access_token' => $request->access_token,
            'webhook_url' => $request->webhook_url,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Facebook App Configuration updated successfully!',
            'data' => $configuration->load('branch')
        ], 200);
    }

    // Delete Facebook App Configuration
    public function destroy($id)
    {
        $configuration = FacebookAppConfiguration::where('id', $id)
            ->where('isDeleted', 0)
            ->first();

        if (!$configuration) {
            return response()->json([
                'status' => false,
                'message' => 'Configuration not found'
            ], 404);
        }

        $configuration->update(['isDeleted' => 1]);

        return response()->json([
            'status' => true,
            'message' => 'Facebook App Configuration deleted successfully!'
        ], 200);
    }

    // Fetch message templates from Facebook Graph API
    public function getMessageTemplates(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role;
        $userBranchId = $user->id;
        $BranchId = $user->branch_id ?? $userBranchId;

        $selectedSubAdminId = $request->query('selectedSubAdminId');
        $branchIdFilter = $request->query('branch_id');

        // Use same query logic as index method for consistency
        $query = FacebookAppConfiguration::where('isDeleted', 0);

        if ($role === 'sub-admin') {
            // Sub-admin: show only their own branch's data
            $query->where('branch_id', $userBranchId);
        } elseif ($role === 'admin' && !empty($selectedSubAdminId) && $selectedSubAdminId !== 'null') {
            // Admin with filter: show selected sub-admin's branch data
            $subAdmin = User::find($selectedSubAdminId);
            if ($subAdmin) {
                $query->where('branch_id', $subAdmin->id);
            } else {
                // If sub-admin not found, use current user's branch (Main Branch)
                $query->where('branch_id', $userBranchId);
            }
        } elseif ($role === 'staff') {
            $query->where('branch_id', $BranchId);
        } elseif ($role === 'admin') {
            // Admin without branch selection: use current user's ID (Main Branch)
            $query->where('branch_id', $userBranchId);
        }

        // Additional branch filter from dropdown
        if ($branchIdFilter && $branchIdFilter !== 'null') {
            $query->where('branch_id', $branchIdFilter);
        }

        $configuration = $query->first();

        if (!$configuration) {
            return response()->json([
                'status' => false,
                'message' => 'Facebook App Configuration not found for this branch'
            ], 404);
        }

        if (empty($configuration->whatsapp_business_account_id) || empty($configuration->access_token)) {
            return response()->json([
                'status' => false,
                'message' => 'Configuration incomplete. WhatsApp Business Account ID and Access Token are required.'
            ], 400);
        }

        try {
            // Fetch message templates from Facebook Graph API
            // Use whatsapp_business_account_id for message_templates endpoint
            $url = "https://graph.facebook.com/v23.0/{$configuration->whatsapp_business_account_id}/message_templates";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $configuration->access_token,
                'Content-Type' => 'application/json',
            ])->get($url);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['data'])) {
                // Save templates to database
                $savedCount = 0;
                $updatedCount = 0;
                $insertedCount = 0;

                foreach ($responseData['data'] as $template) {
                    // Check if template exists by template_id (since it's unique)
                    $existingTemplate = WhatsAppMessageTemplate::where('template_id', $template['id'])->first();

                    // Determine on_off based on status: APPROVED = active, otherwise = inactive
                    $templateStatus = $template['status'] ?? 'UNKNOWN';
                    $onOffStatus = ($templateStatus === 'APPROVED') ? 'active' : 'inactive';

                    if ($existingTemplate) {
                        // Update existing template
                        $existingTemplate->update([
                            'facebook_app_configuration_id' => $configuration->id,
                            'branch_id' => $configuration->branch_id,
                            'name' => $template['name'] ?? '',
                            'status' => $templateStatus,
                            'on_off' => $onOffStatus, // Set based on status
                            'language' => $template['language'] ?? null,
                            'category' => $template['category'] ?? null,
                            'sub_category' => $template['sub_category'] ?? null,
                            'components' => json_encode($template['components'] ?? []),
                            'isDeleted' => 0,
                        ]);
                        $updatedCount++;
                    } else {
                        // Insert new template with on_off based on status
                        WhatsAppMessageTemplate::create([
                            'facebook_app_configuration_id' => $configuration->id,
                            'branch_id' => $configuration->branch_id,
                            'template_id' => $template['id'],
                            'name' => $template['name'] ?? '',
                            'status' => $templateStatus,
                            'on_off' => $onOffStatus, // Set based on status: APPROVED = active, otherwise = inactive
                            'language' => $template['language'] ?? null,
                            'category' => $template['category'] ?? null,
                            'sub_category' => $template['sub_category'] ?? null,
                            'components' => json_encode($template['components'] ?? []),
                            'isDeleted' => 0,
                        ]);
                        $insertedCount++;
                    }
                    $savedCount++;
                }

                Log::info("Message templates saved to database", [
                    'branch_id' => $configuration->branch_id,
                    'total' => $savedCount,
                    'inserted' => $insertedCount,
                    'updated' => $updatedCount
                ]);

                return response()->json([
                    'status' => true,
                    'data' => $responseData['data'],
                    'paging' => $responseData['paging'] ?? null,
                    'saved_count' => $savedCount,
                    'inserted_count' => $insertedCount,
                    'updated_count' => $updatedCount,
                    'message' => "Successfully fetched and processed {$savedCount} templates ({$insertedCount} inserted, {$updatedCount} updated)"
                ], 200);
            } else {
                Log::error("Facebook Graph API error", [
                    'branch_id' => $configuration->branch_id,
                    'response' => $responseData,
                    'status' => $response->status()
                ]);

                $errorCode = $responseData['error']['code'] ?? null;
                $errorSubCode = $responseData['error']['error_subcode'] ?? null;
                $errorMessage = $responseData['error']['message'] ?? 'Failed to fetch message templates';

                // Handle access token expiration
                if ($errorCode == 190) {
                    if ($errorSubCode == 463) {
                        $errorMessage = 'Access token has expired. Please update your Facebook App Configuration access token in the Configuration tab.';
                    } else {
                        $errorMessage = 'Access token is invalid or expired. Please update your Facebook App Configuration access token in the Configuration tab.';
                    }
                }

                return response()->json([
                    'status' => false,
                    'message' => $errorMessage,
                    'error' => $responseData['error'] ?? null,
                    'error_code' => $errorCode,
                    'error_subcode' => $errorSubCode
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Message templates fetch exception", [
                'branch_id' => $configuration->branch_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ], 500);
        }
    }

    // Store templates in database (called from frontend after fetching from Facebook)
    public function storeTemplates(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role;
        $userBranchId = $user->id;
        $BranchId = $user->branch_id ?? $userBranchId;

        $selectedSubAdminId = $request->selectedSubAdminId;
        $branchIdFilter = $request->branch_id;

        // Determine branch_id
        $branchId = null;
        if (!empty($selectedSubAdminId) && $selectedSubAdminId !== 'null' && $selectedSubAdminId !== '') {
            $branchId = $selectedSubAdminId;
        } elseif (!empty($branchIdFilter)) {
            $branchId = $branchIdFilter;
        } elseif ($role === 'sub-admin') {
            $branchId = $userBranchId;
        } elseif ($role === 'staff') {
            $branchId = $BranchId;
        } elseif ($role === 'admin') {
            $branchId = $userBranchId;
        }

        // Get configuration for this branch
        $query = FacebookAppConfiguration::where('isDeleted', 0);
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        $configuration = $query->first();

        if (!$configuration) {
            return response()->json([
                'status' => false,
                'message' => 'Facebook App Configuration not found for this branch'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'templates' => 'required|array',
            'templates.*.id' => 'required|string',
            'templates.*.name' => 'required|string',
            'templates.*.status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $savedCount = 0;
        $updatedCount = 0;
        $insertedCount = 0;

        foreach ($request->templates as $template) {
            // Check if template exists by template_id (since it's unique)
            $existingTemplate = WhatsAppMessageTemplate::where('template_id', $template['id'])->first();

            // Determine on_off based on status: APPROVED = active, otherwise = inactive
            $templateStatus = $template['status'] ?? 'UNKNOWN';
            $onOffStatus = ($templateStatus === 'APPROVED') ? 'active' : 'inactive';

            if ($existingTemplate) {
                // Update existing template
                $existingTemplate->update([
                    'facebook_app_configuration_id' => $configuration->id,
                    'branch_id' => $branchId,
                    'name' => $template['name'] ?? '',
                    'status' => $templateStatus,
                    'on_off' => $onOffStatus, // Set based on status
                    'language' => $template['language'] ?? null,
                    'category' => $template['category'] ?? null,
                    'sub_category' => $template['sub_category'] ?? null,
                    'components' => json_encode($template['components'] ?? []),
                    'isDeleted' => 0,
                ]);
                $updatedCount++;
            } else {
                // Insert new template with on_off based on status
                WhatsAppMessageTemplate::create([
                    'facebook_app_configuration_id' => $configuration->id,
                    'branch_id' => $branchId,
                    'template_id' => $template['id'],
                    'name' => $template['name'] ?? '',
                    'status' => $templateStatus,
                    'on_off' => $onOffStatus, // Set based on status: APPROVED = active, otherwise = inactive
                    'language' => $template['language'] ?? null,
                    'category' => $template['category'] ?? null,
                    'sub_category' => $template['sub_category'] ?? null,
                    'components' => json_encode($template['components'] ?? []),
                    'isDeleted' => 0,
                ]);
                $insertedCount++;
            }
            $savedCount++;
        }

        return response()->json([
            'status' => true,
            'message' => "Successfully processed {$savedCount} templates ({$insertedCount} inserted, {$updatedCount} updated)",
            'saved_count' => $savedCount,
            'inserted_count' => $insertedCount,
            'updated_count' => $updatedCount
        ], 200);
    }

    // Get stored templates from database
    public function getStoredTemplates(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role;
        $userBranchId = $user->id;
        $BranchId = $user->branch_id ?? $userBranchId;

        $selectedSubAdminId = $request->query('selectedSubAdminId');
        $branchIdFilter = $request->query('branch_id');

        // Determine branch_id
        $branchId = null;
        if (!empty($selectedSubAdminId) && $selectedSubAdminId !== 'null' && $selectedSubAdminId !== '') {
            $branchId = $selectedSubAdminId;
        } elseif (!empty($branchIdFilter)) {
            $branchId = $branchIdFilter;
        } elseif ($role === 'sub-admin') {
            $branchId = $userBranchId;
        } elseif ($role === 'staff') {
            $branchId = $BranchId;
        } elseif ($role === 'admin') {
            $branchId = $userBranchId;
        }

        $query = WhatsAppMessageTemplate::where('isDeleted', 0);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $templates = $query->orderBy('name', 'asc')->get();

        // Format templates to match Facebook API response format
        $formattedTemplates = $templates->map(function($template) {
            return [
                'id' => $template->template_id,
                'name' => $template->name,
                'status' => $template->status,
                'on_off' => $template->on_off ?? 'inactive',
                'use_for_template' => $template->use_for_template ?? null,
                'language' => $template->language,
                'category' => $template->category,
                'sub_category' => $template->sub_category,
                'components' => is_string($template->components) ? json_decode($template->components, true) : $template->components,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $formattedTemplates,
            'count' => $formattedTemplates->count()
        ], 200);
    }

    // Toggle template on_off status
    public function toggleTemplateStatus(Request $request, $templateId)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role;
        $userBranchId = $user->id;
        $BranchId = $user->branch_id ?? $userBranchId;

        $selectedSubAdminId = $request->selectedSubAdminId;
        $branchIdFilter = $request->branch_id;

        // Determine branch_id
        $branchId = null;
        if (!empty($selectedSubAdminId) && $selectedSubAdminId !== 'null' && $selectedSubAdminId !== '') {
            $branchId = $selectedSubAdminId;
        } elseif (!empty($branchIdFilter)) {
            $branchId = $branchIdFilter;
        } elseif ($role === 'sub-admin') {
            $branchId = $userBranchId;
        } elseif ($role === 'staff') {
            $branchId = $BranchId;
        } elseif ($role === 'admin') {
            $branchId = $userBranchId;
        }

        $validator = Validator::make($request->all(), [
            'on_off' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = WhatsAppMessageTemplate::where('template_id', $templateId)
            ->where('isDeleted', 0);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $template = $query->first();

        if (!$template) {
            return response()->json([
                'status' => false,
                'message' => 'Template not found'
            ], 404);
        }

        // Only allow toggle if template status is APPROVED
        if ($template->status !== 'APPROVED') {
            return response()->json([
                'status' => false,
                'message' => 'Only APPROVED templates can be toggled to active/inactive. Current status: ' . $template->status
            ], 422);
        }

        $template->update([
            'on_off' => $request->on_off
        ]);

        return response()->json([
            'status' => true,
            'message' => "Template status updated to {$request->on_off}",
            'data' => [
                'template_id' => $template->template_id,
                'name' => $template->name,
                'on_off' => $template->on_off
            ]
        ], 200);
    }

    // Update template use_for_template
    public function updateTemplateUseFor(Request $request, $templateId)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role;
        $userBranchId = $user->id;
        $BranchId = $user->branch_id ?? $userBranchId;

        $selectedSubAdminId = $request->selectedSubAdminId;
        $branchIdFilter = $request->branch_id;

        // Determine branch_id
        $branchId = null;
        if (!empty($selectedSubAdminId) && $selectedSubAdminId !== 'null' && $selectedSubAdminId !== '') {
            $branchId = $selectedSubAdminId;
        } elseif (!empty($branchIdFilter)) {
            $branchId = $branchIdFilter;
        } elseif ($role === 'sub-admin') {
            $branchId = $userBranchId;
        } elseif ($role === 'staff') {
            $branchId = $BranchId;
        } elseif ($role === 'admin') {
            $branchId = $userBranchId;
        }

        $validator = Validator::make($request->all(), [
            'use_for_template' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = WhatsAppMessageTemplate::where('template_id', $templateId)
            ->where('isDeleted', 0);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $template = $query->first();

        if (!$template) {
            return response()->json([
                'status' => false,
                'message' => 'Template not found'
            ], 404);
        }

        $template->update([
            'use_for_template' => $request->use_for_template ?: null
        ]);

        return response()->json([
            'status' => true,
            'message' => "Template updated successfully",
            'data' => [
                'template_id' => $template->template_id,
                'name' => $template->name,
                'use_for_template' => $template->use_for_template
            ]
        ], 200);
    }
}
