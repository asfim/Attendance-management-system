<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CMSController extends Controller
{
    public function home()
    {
        $settings = Setting::where('key', 'like', 'cms_home_%')->pluck('value', 'key')->toArray();
        return view('admin.cms.home', compact('settings'));
    }

    public function updateHome(Request $request)
    {
        $data = $request->except(['_token']);

        // Handle image uploads
        $imageFields = [
            'cms_home_hero_bg_1',
            'cms_home_hero_bg_2',
            'cms_home_hero_bg_3',
            'cms_home_chairman_photo',
            'cms_home_principal_photo',
            'cms_home_stat_bg',
            'cms_home_test_photo_1',
            'cms_home_test_photo_2',
            'cms_home_test_photo_3'
        ];

        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $path = $file->store('cms', 'public');
                // Save old path for deletion if needed (skipped for simplicity)
                $data[$field] = '/storage/' . $path;
            } else {
                // If no new file uploaded, keep the old one (remove from $data so it doesn't overwrite)
                unset($data[$field]);
            }
        }

        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $val = json_encode($val);
            }
            if ($val !== null) {
                Setting::set($key, $val);
            }
        }

        return back()->with('success', 'Homepage settings updated successfully!');
    }

    public function about()
    {
        $settings = Setting::where('key', 'like', 'about_%')->pluck('value', 'key')->toArray();
        return view('admin.cms.about', compact('settings'));
    }

    public function updateAbout(Request $request)
    {
        $data = $request->except(['_token']);

        if ($request->hasFile('about_welcome_image')) {
            $path = $request->file('about_welcome_image')->store('cms', 'public');
            $data['about_welcome_image'] = '/storage/' . $path;
        }

        if ($request->hasFile('about_hero_bg')) {
            $path = $request->file('about_hero_bg')->store('cms', 'public');
            $data['about_hero_bg'] = '/storage/' . $path;
        }

        foreach ($data as $key => $val) {
            if ($val !== null) Setting::set($key, $val);
        }
        return back()->with('success', 'About page settings updated successfully!');
    }

    public function principalMessage()
    {
        $settings = Setting::where('key', 'like', 'leadership_%')->pluck('value', 'key')->toArray();
        return view('admin.cms.principal-message', compact('settings'));
    }

    public function updatePrincipalMessage(Request $request)
    {
        $data = $request->except(['_token']);

        if ($request->hasFile('leadership_hero_bg')) {
            $path = $request->file('leadership_hero_bg')->store('cms', 'public');
            $data['leadership_hero_bg'] = '/storage/' . $path;
        }

        if ($request->hasFile('leadership_chairman_photo')) {
            $path = $request->file('leadership_chairman_photo')->store('cms', 'public');
            $data['leadership_chairman_photo'] = '/storage/' . $path;
        }

        if ($request->hasFile('leadership_principal_photo')) {
            $path = $request->file('leadership_principal_photo')->store('cms', 'public');
            $data['leadership_principal_photo'] = '/storage/' . $path;
        }

        foreach ($data as $key => $val) {
            if ($val !== null) Setting::set($key, $val);
        }
        return back()->with('success', 'Principal Message page settings updated successfully!');
    }

    public function academics()
    {
        $settings = Setting::where('key', 'like', 'academics_%')->pluck('value', 'key')->toArray();
        return view('admin.cms.academics', compact('settings'));
    }

    public function updateAcademics(Request $request)
    {
        $data = $request->except(['_token']);

        if ($request->hasFile('academics_hero_bg')) {
            $path = $request->file('academics_hero_bg')->store('cms', 'public');
            $data['academics_hero_bg'] = '/storage/' . $path;
        }
        
        if ($request->hasFile('academics_methodology_image')) {
            $path = $request->file('academics_methodology_image')->store('cms', 'public');
            $data['academics_methodology_image'] = '/storage/' . $path;
        }

        foreach ($data as $key => $val) {
            if ($val !== null) {
                if (is_array($val)) {
                    $val = json_encode(array_values($val));
                }
                Setting::set($key, $val);
            }
        }
        return back()->with('success', 'Academics page settings updated successfully!');
    }

    public function departments()
    {
        $settings = Setting::where('key', 'like', 'departments_%')->pluck('value', 'key')->toArray();
        return view('admin.cms.departments', compact('settings'));
    }

    public function updateDepartments(Request $request)
    {
        $data = $request->except(['_token']);

        if ($request->hasFile('departments_hero_bg')) {
            $path = $request->file('departments_hero_bg')->store('cms', 'public');
            $data['departments_hero_bg'] = '/storage/' . $path;
        }

        foreach ($data as $key => $val) {
            if ($val !== null) {
                if (is_array($val)) {
                    $val = json_encode(array_values($val));
                }
                Setting::set($key, $val);
            }
        }
        return back()->with('success', 'Departments page settings updated successfully!');
    }

    public function calendar()
    {
        $settings = Setting::where('key', 'like', 'cms_calendar_%')->pluck('value', 'key')->toArray();
        return view('admin.cms.calendar', compact('settings'));
    }

    public function updateCalendar(Request $request)
    {
        $data = $request->except(['_token', 'events', 'legends']);
        
        if ($request->hasFile('cms_calendar_pdf')) {
            $path = $request->file('cms_calendar_pdf')->store('cms/calendar', 'public');
            $data['cms_calendar_pdf'] = '/storage/' . $path;
        }

        $events = $request->input('events', []);
        $finalEvents = [];
        foreach ($events as $index => $item) {
            if (isset($item['delete']) && $item['delete'] == '1') {
                continue;
            }
            if (!empty($item['date']) && !empty($item['title'])) {
                $finalEvents[] = [
                    'date'  => $item['date'],
                    'day'   => $item['day'] ?? '',
                    'title' => $item['title'],
                    'desc'  => $item['desc'] ?? '',
                    'type'  => $item['type'] ?? 'academic',
                ];
            }
        }
        $data['cms_calendar_events'] = json_encode($finalEvents);

        $legends = $request->input('legends', []);
        $finalLegends = [];
        foreach ($legends as $index => $item) {
            if (isset($item['delete']) && $item['delete'] == '1') {
                continue;
            }
            if (!empty($item['key']) && !empty($item['label'])) {
                $finalLegends[] = [
                    'key'        => $item['key'],
                    'label'      => $item['label'],
                    'icon'       => $item['icon'] ?? '',
                    'bg_color'   => $item['bg_color'] ?? '#E1EFFE',
                    'text_color' => $item['text_color'] ?? '#1E429F',
                ];
            }
        }
        $data['cms_calendar_legends'] = json_encode($finalLegends);

        foreach ($data as $key => $val) {
            if ($val !== null) Setting::set($key, $val);
        }

        return back()->with('success', 'Calendar page settings updated successfully!');
    }

    public function campus()
    {
        $settings = Setting::where('key', 'like', 'campus_%')->pluck('value', 'key')->toArray();
        return view('admin.cms.campus', compact('settings'));
    }

    public function updateCampus(Request $request)
    {
        $data = $request->except(['_token', 'gallery']);
        
        // Handle single hero background image
        if ($request->hasFile('campus_hero_bg')) {
            $path = $request->file('campus_hero_bg')->store('cms/campus', 'public');
            $data['campus_hero_bg'] = '/storage/' . $path;
        } else {
            unset($data['campus_hero_bg']);
        }

        // Handle dynamic gallery
        $gallery = $request->input('gallery', []);
        $finalGallery = [];
        foreach ($gallery as $index => $item) {
            $imagePath = $item['old_image'] ?? null;
            
            // If the item is marked for deletion, skip it
            if (isset($item['delete']) && $item['delete'] == '1') {
                continue;
            }

            if ($request->hasFile("gallery.{$index}.image")) {
                $path = $request->file("gallery.{$index}.image")->store('cms/campus', 'public');
                $imagePath = '/storage/' . $path;
            }

            if ($imagePath) {
                $finalGallery[] = [
                    'image' => $imagePath,
                    'category' => $item['category'] ?? ''
                ];
            }
        }
        $data['campus_gallery'] = json_encode($finalGallery);

        foreach ($data as $key => $val) {
            if ($val !== null) Setting::set($key, $val);
        }
        return back()->with('success', 'Campus page settings updated successfully!');
    }

    public function gallery()
    {
        $settings = Setting::where('key', 'like', 'gallery_%')->pluck('value', 'key')->toArray();
        return view('admin.cms.gallery', compact('settings'));
    }

    public function updateGallery(Request $request)
    {
        $data = $request->except(['_token', 'gallery']);

        // Handle dynamic gallery items
        $gallery = $request->input('gallery', []);
        $finalGallery = [];
        foreach ($gallery as $index => $item) {
            $imagePath = $item['old_image'] ?? null;

            // If marked for deletion, skip it
            if (isset($item['delete']) && $item['delete'] == '1') {
                continue;
            }

            if ($request->hasFile("gallery.{$index}.image")) {
                $path = $request->file("gallery.{$index}.image")->store('cms/gallery', 'public');
                $imagePath = '/storage/' . $path;
            }

            if ($imagePath) {
                $finalGallery[] = [
                    'image'    => $imagePath,
                    'title'    => $item['title'] ?? '',
                    'category' => !empty($item['category']) ? $item['category'] : 'General',
                    'size'     => $item['size'] ?? 'normal',
                ];
            }
        }

        $data['gallery_data'] = json_encode($finalGallery);

        foreach ($data as $key => $val) {
            if ($val !== null) Setting::set($key, $val);
        }

        return back()->with('success', 'Gallery updated successfully!');
    }

    public function contact()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $unreadCount = \App\Models\ContactMessage::where('is_read', false)->count();
        return view('admin.cms.contact', compact('settings', 'unreadCount'));
    }

    public function updateContact(Request $request)
    {
        $data = $request->except(['_token']);
        foreach ($data as $key => $val) {
            if ($val !== null) Setting::set($key, $val);
        }
        return back()->with('success', 'Contact page settings updated successfully!');
    }

    public function contactMessages(Request $request)
    {
        $query = \App\Models\ContactMessage::latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->status === 'read') {
                $query->where('is_read', true);
            }
        }

        $messages = $query->paginate(15)->withQueryString();
        $unreadCount = \App\Models\ContactMessage::where('is_read', false)->count();

        return view('admin.cms.contact_messages.index', compact('messages', 'unreadCount'));
    }

    public function showContactMessage($id)
    {
        $message = \App\Models\ContactMessage::findOrFail($id);
        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }
        return view('admin.cms.contact_messages.show', compact('message'));
    }

    public function destroyContactMessage($id)
    {
        $message = \App\Models\ContactMessage::findOrFail($id);
        $message->delete();
        return redirect()->route('admin.cms.contact.messages')->with('success', 'Contact message deleted successfully.');
    }

    public function footer()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.cms.footer', compact('settings'));
    }

    public function updateFooter(Request $request)
    {
        $data = $request->except(['_token']);

        if ($request->hasFile('site_logo')) {
            $path = $request->file('site_logo')->store('cms/logo', 'public');
            $data['site_logo'] = '/storage/' . $path;
        }

        foreach ($data as $key => $val) {
            if ($val !== null) Setting::set($key, $val);
        }

        return back()->with('success', 'Footer settings updated successfully!');
    }
}
