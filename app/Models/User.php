<?php

namespace App\Models;

use App\Models\BloodGroup;
use App\Models\Lga;
use App\Models\Nationality;
use App\Models\StaffRecord;
use App\Models\State;
use App\Models\StudentRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'email', 'phone', 'phone2', 'dob', 'gender', 'photo', 'address', 
        'medical_info', 'password', 'nal_id', 'state_id', 'lga_id', 'code', 'user_type', 
        'email_verified_at',
        // NEW FIELDS FOR ZIMBABWEAN GEOGRAPHY AND MEDICAL HISTORY
        'medical_history',
        'medical_conditions',
        'allergies',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'province',
        'district',
        'nationality'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'dob' => 'date',
        'medical_conditions' => 'array',
        'allergies' => 'array',
    ];

    public function student_record()
    {
        return $this->hasOne(StudentRecord::class);
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class, 'nal_id');
    }

    public function blood_group()
    {
        return $this->belongsTo(BloodGroup::class, 'bg_id');
    }

    public function staff()
    {
        return $this->hasMany(StaffRecord::class);
    }

    /*****************************************************************
     * NEW METHODS FOR ZIMBABWEAN GEOGRAPHY AND MEDICAL HISTORY
     *****************************************************************/

    /**
     * Check if user is a student
     */
    public function isStudent()
    {
        return $this->user_type === 'student';
    }

    /**
     * Check if user is a teacher
     */
    public function isTeacher()
    {
        return $this->user_type === 'teacher';
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin()
    {
        return $this->user_type === 'admin' || $this->user_type === 'super_admin';
    }

    /**
     * Check if user is a parent
     */
    public function isParent()
    {
        return $this->user_type === 'parent';
    }

    /**
     * Get user's display name with title
     */
    public function getDisplayNameAttribute()
    {
        $title = $this->isStudent() ? 'Student' : 
                ($this->isTeacher() ? 'Teacher' : 
                ($this->isAdmin() ? 'Admin' : 
                ($this->isParent() ? 'Parent' : 'User')));
        
        return $this->name . ' (' . $title . ')';
    }

    /**
     * Get age from date of birth
     */
    public function getAgeAttribute()
    {
        if (!$this->dob) {
            return null;
        }
        
        return now()->diffInYears($this->dob);
    }

    /**
     * Get formatted medical conditions
     */
    public function getFormattedMedicalConditionsAttribute()
    {
        if (!$this->medical_conditions) {
            return 'None';
        }
        
        if (is_array($this->medical_conditions)) {
            return implode(', ', $this->medical_conditions);
        }
        
        return $this->medical_conditions;
    }

    /**
     * Get formatted allergies
     */
    public function getFormattedAllergiesAttribute()
    {
        if (!$this->allergies) {
            return 'None';
        }
        
        if (is_array($this->allergies)) {
            return implode(', ', $this->allergies);
        }
        
        return $this->allergies;
    }

    /**
     * Get emergency contact info
     */
    public function getEmergencyContactAttribute()
    {
        if (!$this->emergency_contact_name) {
            return 'Not set';
        }
        
        return $this->emergency_contact_name . ' (' . $this->emergency_contact_relationship . ') - ' . $this->emergency_contact_phone;
    }

    /**
     * Get full address with province and district
     */
    public function getFullAddressAttribute()
    {
        $address = $this->address;
        
        if ($this->district) {
            $address .= ', ' . $this->district;
        }
        
        if ($this->province) {
            $address .= ', ' . $this->province;
        }
        
        return $address;
    }

    /**
     * Get Zimbabwean province name
     */
    public function getProvinceNameAttribute()
    {
        $provinces = [
            'Bulawayo' => 'Bulawayo',
            'Harare' => 'Harare',
            'Manicaland' => 'Manicaland',
            'Mashonaland Central' => 'Mashonaland Central',
            'Mashonaland East' => 'Mashonaland East',
            'Mashonaland West' => 'Mashonaland West',
            'Masvingo' => 'Masvingo',
            'Matabeleland North' => 'Matabeleland North',
            'Matabeleland South' => 'Matabeleland South',
            'Midlands' => 'Midlands'
        ];
        
        return $provinces[$this->province] ?? $this->province;
    }

    /**
     * Check if user has medical conditions
     */
    public function hasMedicalConditions()
    {
        return !empty($this->medical_conditions) && 
               $this->medical_conditions != 'None' && 
               $this->medical_conditions != '[]';
    }

    /**
     * Check if user has allergies
     */
    public function hasAllergies()
    {
        return !empty($this->allergies) && 
               $this->allergies != 'None' && 
               $this->allergies != '[]';
    }

    /**
     * Scope query for students
     */
    public function scopeStudents($query)
    {
        return $query->where('user_type', 'student');
    }

    /**
     * Scope query for teachers
     */
    public function scopeTeachers($query)
    {
        return $query->where('user_type', 'teacher');
    }

    /**
     * Scope query for admins
     */
    public function scopeAdmins($query)
    {
        return $query->whereIn('user_type', ['admin', 'super_admin']);
    }

    /**
     * Scope query for parents
     */
    public function scopeParents($query)
    {
        return $query->where('user_type', 'parent');
    }

    /**
     * Search users by name, email, or phone
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
    }

    /**
     * Get users by Zimbabwean province
     */
    public function scopeByProvince($query, $province)
    {
        return $query->where('province', $province);
    }

    /**
     * Get users by Zimbabwean district
     */
    public function scopeByDistrict($query, $district)
    {
        return $query->where('district', $district);
    }

    /**
     * Get users with medical conditions
     */
    public function scopeWithMedicalConditions($query)
    {
        return $query->whereNotNull('medical_conditions')
                    ->where('medical_conditions', '!=', '')
                    ->where('medical_conditions', '!=', 'None')
                    ->where('medical_conditions', '!=', '[]');
    }

    /**
     * Get users with allergies
     */
    public function scopeWithAllergies($query)
    {
        return $query->whereNotNull('allergies')
                    ->where('allergies', '!=', '')
                    ->where('allergies', '!=', 'None')
                    ->where('allergies', '!=', '[]');
    }

    /**
     * Get users by medical condition type
     */
    public function scopeWithMedicalCondition($query, $condition)
    {
        return $query->where('medical_conditions', 'like', "%{$condition}%");
    }

    /**
     * Get users by allergy type
     */
    public function scopeWithAllergy($query, $allergy)
    {
        return $query->where('allergies', 'like', "%{$allergy}%");
    }

    /**
     * Get active users (not deleted)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Get user's class/section if student
     */
    public function getClassInfoAttribute()
    {
        if ($this->isStudent() && $this->student_record) {
            $class = $this->student_record->myClass ?? null;
            $section = $this->student_record->section ?? null;
            
            return [
                'class_name' => $class ? $class->name : 'N/A',
                'section_name' => $section ? $section->name : 'N/A',
                'admission_no' => $this->student_record->adm_no ?? 'N/A',
                'year' => $this->student_record->year_admitted ?? 'N/A'
            ];
        }
        
        return null;
    }

    /**
     * Get user's role badge class for UI
     */
    public function getRoleBadgeClassAttribute()
    {
        $classes = [
            'student' => 'badge-primary',
            'teacher' => 'badge-success',
            'admin' => 'badge-danger',
            'super_admin' => 'badge-danger',
            'parent' => 'badge-warning'
        ];
        
        return $classes[$this->user_type] ?? 'badge-secondary';
    }

    /**
     * Get user's role display name
     */
    public function getRoleNameAttribute()
    {
        $roles = [
            'student' => 'Student',
            'teacher' => 'Teacher',
            'admin' => 'Administrator',
            'super_admin' => 'Super Administrator',
            'parent' => 'Parent'
        ];
        
        return $roles[$this->user_type] ?? 'User';
    }
}