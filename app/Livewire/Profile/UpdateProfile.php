<?php

namespace App\Livewire\Profile;

use App\Livewire\AppComponent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Intervention\Image\Laravel\Facades\Image;

class UpdateProfile extends AppComponent
{
    use WithFileUploads;

    public $name;
    public $email;
    public $current_password;
    public $password;
    public $password_confirmation;
    public $avatar;
    public $deleteAccount = false;
    public $deleteConfirmPassword;
    public $photo;
    public $showDeleteModal = false;

    protected $rules = [
        'avatar' => 'nullable|image|max:1024|mimes:jpg,jpeg,png|dimensions:min_width=100,min_height=100',
    ];

    protected $messages = [
        'avatar.max' => 'The image must not be larger than 1MB.',
        'avatar.dimensions' => 'The image must be at least 100x100 pixels.',
        'avatar.mimes' => 'The image must be a JPG, JPEG or PNG file.',
    ];

    public function mount()
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function updateProfile()
    {
        $user = auth()->user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'max:1024', 'mimes:jpg,jpeg,png', 'dimensions:min_width=100,min_height=100'],
        ]);

        if ($this->avatar) {
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Process and optimize the image
            $image = Image::read($this->avatar->path());
            
            // Resize if larger than 300x300 while maintaining aspect ratio
            if ($image->width() > 300 || $image->height() > 300) {
                $image->scaleDown(300, 300);
            }

            // Convert to JPG and optimize
            $image->encodeByExtension('jpg', 80);

            // Generate unique filename
            $filename = 'avatars/' . uniqid() . '.jpg';
            
            // Save the optimized image
            Storage::disk('public')->put($filename, $image->toJpeg(80));
            
            $validated['avatar'] = $filename;
        }

        $user->update($validated);

        $this->dispatch('profile-updated');
    }

    public function deleteAvatar()
    {
        $user = auth()->user();
        
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);
        $this->dispatch('profile-updated');
    }

    public function updatePassword()
    {
        $validated = $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        auth()->user()->update([
            'password' => bcrypt($validated['password'])
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->dispatch('password-updated');
    }

    public function confirmAccountDeletion()
    {
        $this->validate([
            'deleteConfirmPassword' => ['required', 'current_password'],
        ]);

        $user = auth()->user();

        try {
            // Begin transaction
            \DB::beginTransaction();

            // Delete avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Delete all related records
            $user->expenses()->delete();
            $user->budgets()->delete();
            
            // Delete the user
            $user->delete();

            \DB::commit();

            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Account deleted successfully.');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->addError('deleteConfirmPassword', 'Failed to delete account. Please try again.');
            return;
        }
    }

    public function updateProfilePhoto()
    {
        $this->validate([
            'photo' => 'required|image|max:1024', // 1MB Max
        ]);

        $path = $this->photo->store('avatars', 'public');

        if (auth()->user()->avatar) {
            Storage::disk('public')->delete(auth()->user()->avatar);
        }

        auth()->user()->update([
            'avatar' => $path
        ]);

        $this->dispatch('profile-updated');
    }

    protected function view(): string
    {
        return 'livewire.profile.update-profile';
    }
} 