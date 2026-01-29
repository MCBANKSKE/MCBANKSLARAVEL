<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
//only import if you have the models
//use App\Models\Member;
//use App\Models\MemberAccount;
//use App\Models\Country;
//use App\Models\State;
use App\Services\EmailService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Session;

/**
 * Multi-step Registration Form
 *
 * INSTRUCTIONS FOR DEVELOPERS:
 * ----------------------------
 * 1. Include this component in your Blade file:
 *      <livewire:auth.registration-form />
 *
 * 2. Database setup:
 *      - Ensure users table exists with 'is_super_admin' column.
 *      - Run Spatie migrations for roles/permissions.
 *
 * 3. Roles:
 *      - Available roles: member, customer, manager, admin.
 *      - Default role is 'member' if not selected.
 *      - Create roles first:
 *          php artisan role:create admin
 *          php artisan role:create member
 *          php artisan role:create manager
 *          php artisan role:create customer
 *
 * 4. Optional profile fields:
 *      - You can remove fields you don’t use (gender, DOB, phone, address, location)
 *      - Update $rules array accordingly.
 *
 * 5. Email verification:
 *      - User must verify email (Laravel must implement MustVerifyEmail on User model)
 *
 * 6. Customization:
 *      - Update createMemberProfile(), createCustomerProfile(), createManagerProfile() to add custom fields.
 *      - Adjust Blade view to include fields you need.
 */
class RegistrationForm extends Component
{
    public $currentStep = 1;

    // Step 1: User account info
    public $user = [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => ''
    ];

    // Role selection (optional)
    public $selected_role = 'member';

    // Optional profile fields (remove if not needed)
    public $gender = '';
    public $date_of_birth = '';
    public $nationality_id = '';
    public $state_id = '';
    public $city = '';
    public $zip_code = '';
    public $address = '';
    public $phone = '';
    public $terms = false;

    // Dynamic states based on country
    public $states = [];

    /**
     * Validation rules
     * ----------------
     * Customize by removing unused fields, adding new fields, or changing 'required' rules
     */
    protected $rules = [
        'user.name' => ['required', 'string', 'max:255'],
        'user.email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'user.password' => ['required', 'string', 'min:8', 'confirmed'],
        'gender' => ['nullable', 'string', 'max:20'],
        'date_of_birth' => ['nullable', 'date'],
        'nationality_id' => ['nullable', 'exists:countries,id'],
        'state_id' => ['nullable', 'exists:states,id'],
        'city' => ['nullable', 'string', 'max:255'],
        'zip_code' => ['nullable', 'string', 'max:20'],
        'address' => ['nullable', 'string'],
        'phone' => ['nullable', 'string', 'max:20'],
        'terms' => ['accepted'],
        'selected_role' => ['nullable', 'string', 'in:member,customer,manager,admin'],
    ];

    protected $messages = [
        'terms.accepted' => 'You must accept the terms and conditions to continue.',
    ];

    public function mount()
    {
        $this->states = collect();
    }

    /**
     * Navigate to next step
     * ---------------------
     * Validates current step before moving forward
     */
    public function nextStep()
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'user.name' => $this->rules['user.name'],
                'user.email' => $this->rules['user.email'],
                'user.password' => $this->rules['user.password'],
            ]);
        } elseif ($this->currentStep === 2) {
            $profileValidation = [
                'terms' => $this->rules['terms'],
            ];
            $this->validate($profileValidation);
        }

        $this->currentStep++;
    }

    public function previousStep()
    {
        $this->currentStep--;
    }

    /**
     * Update states when nationality changes
     * --------------------------------------
     * Uncomment this method only if you have Country/State models
     * Remove entirely if you don't need country/state selection
     */
    public function updatedNationalityId($value)
    {
        // Uncomment below if you have Country and State models
        /*
        if ($value) {
            $this->states = State::where('country_id', $value)->get();
            $this->state_id = '';
        } else {
            $this->states = collect();
            $this->state_id = '';
        }
        $this->dispatch('nationalityUpdated');
        */
        
        // If you don't use country/state selection, leave this method empty
    }

    /**
     * Register user
     * --------------
     * Role-based registration with profile creation
     */
    public function register()
    {
        $validated = $this->validate();

        // Create user account
        $user = User::create([
            'name' => $validated['user']['name'],
            'email' => $validated['user']['email'],
            'password' => Hash::make($validated['user']['password']),
            'email_verified_at' => null,
        ]);

        // Determine role
        $role = $validated['selected_role'] ?? 'member';

        // Create role-specific profile
        switch ($role) {
            case 'member':
                $this->createMemberProfile($user, $validated);
                break;
            case 'customer':
                $this->createCustomerProfile($user, $validated);
                break;
            case 'manager':
                $this->createManagerProfile($user, $validated);
                break;
            case 'admin':
                // Admin may not need extra profile
                break;
        }

        // Assign role
        $user->assignRole($role);

        // Log in and trigger email verification
        Auth::login($user);
        event(new Registered($user));

        Session::flash('status', 'A verification link has been sent to your email address. Please verify your email to continue.');

        return redirect()->route('verification.notice');
    }

    /**
     * Member profile creation
     * -----------------------
     * Uncomment and update if you have a Member model
     * Remove this method entirely if you don't need member profiles
     */
    private function createMemberProfile($user, $validated)
    {
        // Uncomment the code below if you have a Member model
        /*
        $memberData = [
            'user_id' => $user->id,
            'status' => 'active',
            
            // Add only the fields you need for members
            // 'gender' => $validated['gender'] ?? null,
            // 'date_of_birth' => $validated['date_of_birth'] ?? null,
            // 'nationality_id' => $validated['nationality_id'] ?? null,
            // 'state_id' => $validated['state_id'] ?? null,
            // 'city' => $validated['city'] ?? null,
            // 'zip_code' => $validated['zip_code'] ?? null,
            // 'address' => $validated['address'] ?? null,
            // 'phone' => $validated['phone'] ?? null,
        ];
        
        // Remove null values to avoid database issues
        $memberData = array_filter($memberData, fn($v) => $v !== null);

        $member = new Member($memberData);
        $user->member()->save($member);
        
        // Uncomment below if you have MemberAccount functionality
        // MemberAccount::create([
        //     'member_id' => $member->id,
        //     'total_credit' => 0.00,
        //     'total_debit' => 0.00,
        //     'balance' => 0.00,
        // ]);
        */
        
        // If you don't have a Member model, leave this method empty
        // or remove it entirely
    }

    /**
     * Customer profile creation
     * ------------------------
     * Uncomment and update if you have a Customer model
     * Remove this method entirely if you don't need customer profiles
     */
    private function createCustomerProfile($user, $validated)
    {
        // Uncomment the code below if you have a Customer model
        /*
        Customer::create([
            'user_id' => $user->id,
            
            // Add only the fields you need for customers
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'zip_code' => $validated['zip_code'] ?? null,
            
            // Add customer-specific fields as needed
            // 'customer_type' => 'individual',
            // 'preferences' => [],
        ]);
        */
        
        // If you don't have a Customer model, leave this method empty
        // or remove it entirely
    }

    /**
     * Manager profile creation
     * -----------------------
     * Uncomment and update if you have a Manager model
     * Remove this method entirely if you don't need manager profiles
     */
    private function createManagerProfile($user, $validated)
    {
        // Uncomment the code below if you have a Manager model
        /*
        Manager::create([
            'user_id' => $user->id,
            
            // Add only the fields you need for managers
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? 'general',
            
            // Add manager-specific fields as needed
            // 'employee_id' => uniqid(),
            // 'permissions' => [],
            // 'reports_to' => null,
        ]);
        */
        
        // If you don't have a Manager model, leave this method empty
        // or remove it entirely
    }

    /**
     * Render the registration form view
     * ---------------------------------
     * Note: If you don't have Country/State models, 
     * remove the countries data from the view
     */
    public function render()
    {
        // Uncomment countries data if you have Country model
        // Otherwise, return view without countries
        
        /*
        return view('livewire.auth.registration-form', [
            'countries' => Country::all(),
        ]);
        */
        
        // Simple version without countries/states
        return view('livewire.auth.registration-form');
    }
}
