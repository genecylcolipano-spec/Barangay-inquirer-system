<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $announcements = [
            [
                'title' => 'System Maintenance Notice',
                'excerpt' => 'The barangay inquirer system will undergo scheduled maintenance this weekend. We apologize for any inconvenience.',
                'content' => 'Dear valued residents,

We will be performing scheduled maintenance on our barangay inquirer system this weekend (Saturday, February 15, 2026 from 10:00 PM to 2:00 AM). During this time, the system will be temporarily unavailable.

What to expect:
- System downtime: 10:00 PM - 2:00 AM
- Document requests will be queued and processed after maintenance
- All data will be safely backed up
- No data loss expected

We apologize for any inconvenience this may cause. The maintenance is necessary to improve system performance and add new features.

Thank you for your understanding.

Best regards,
Barangay Inquirer System Team',
                'tag' => 'today',
                'category' => 'maintenance',
                'priority' => 'high',
                'announcement_date' => now(),
                'show_on_homepage' => true,
                'display_order' => 1,
                'icon' => 'fas fa-tools',
                'is_published' => true,
                'created_by' => 6, // Use existing user ID
            ],
            [
                'title' => 'New Document Request Feature',
                'excerpt' => 'We\'ve launched an enhanced document request system with faster processing and real-time tracking capabilities.',
                'content' => 'Exciting news! We have successfully launched our enhanced document request system with the following improvements:

New Features:
- Real-time status tracking
- Faster processing times
- Mobile-friendly interface
- Automated notifications
- Document history tracking
- Priority request options

How it works:
1. Submit your document request through the portal
2. Receive immediate confirmation
3. Track progress in real-time
4. Get notified when ready for pickup
5. Rate our service

This enhancement is part of our commitment to provide better service to our residents. Try it out today!

For questions, contact our support team.',
                'tag' => 'featured',
                'category' => 'feature',
                'priority' => 'normal',
                'announcement_date' => now()->subDays(5),
                'show_on_homepage' => true,
                'display_order' => 2,
                'icon' => 'fas fa-file-plus',
                'is_published' => true,
                'created_by' => 6,
            ],
            [
                'title' => 'Community Program Launch',
                'excerpt' => 'New community development program starts this month with supporting documents now available on the system.',
                'content' => 'We are excited to announce the launch of our new Community Development Program!

Program Highlights:
- Free skills training workshops
- Youth empowerment initiatives
- Environmental conservation projects
- Senior citizen support programs
- Health and wellness activities

Registration is now open for all interested residents. Supporting documents and application forms are available in the system.

Program Schedule:
- Orientation: First week of each month
- Workshops: Saturdays 9:00 AM - 12:00 PM
- Community service: Sundays 8:00 AM - 11:00 AM

Join us in building a stronger, more vibrant community!

For more information, visit the announcements section or contact the barangay office.',
                'tag' => 'success',
                'category' => 'event',
                'priority' => 'normal',
                'announcement_date' => now()->subDays(10),
                'show_on_homepage' => true,
                'display_order' => 3,
                'icon' => 'fas fa-users',
                'is_published' => true,
                'created_by' => 6,
            ],
            [
                'title' => 'Policy Changes: Document Processing',
                'excerpt' => 'Important updates to our document processing policies effective immediately.',
                'content' => 'Important Policy Updates

Effective immediately, we have updated our document processing policies to better serve our community:

New Policies:
1. Express Processing: Available for urgent requests (additional fee applies)
2. Online Verification: Digital verification for employment and school documents
3. Extended Hours: Saturday processing available for select documents
4. Digital Delivery: Electronic delivery option for certain documents

Processing Times:
- Standard documents: 3-5 business days
- Express service: 1-2 business days
- Urgent requests: Same day (conditions apply)

Please review the updated policies in the documents section. We appreciate your understanding and cooperation.

For questions about these changes, please contact our office.',
                'tag' => 'warning',
                'category' => 'policy',
                'priority' => 'high',
                'announcement_date' => now()->subDays(15),
                'show_on_homepage' => false,
                'display_order' => 0,
                'icon' => 'fas fa-gavel',
                'is_published' => true,
                'created_by' => 6,
            ],
            [
                'title' => 'Latest Update: Mobile App Beta',
                'excerpt' => 'Our mobile app is now in beta testing phase. Sign up to be a tester and get early access!',
                'content' => 'Mobile App Beta Launch!

We are excited to announce that our Barangay Inquirer mobile app is now in beta testing!

Beta Features:
- Document request submission
- Real-time status updates
- Push notifications
- Digital document storage
- Emergency contact features
- Community forum access

How to Join Beta Testing:
1. Visit the mobile app section
2. Register your interest
3. Download the beta app
4. Provide feedback through the app

Beta testers will receive:
- Early access to new features
- Priority support
- Exclusive beta community access
- Recognition in our newsletter

Help us improve the app and shape the future of barangay services!

Note: Beta testing is limited to selected users. Registration does not guarantee participation.',
                'tag' => 'info',
                'category' => 'feature',
                'priority' => 'low',
                'announcement_date' => now()->subDays(20),
                'show_on_homepage' => false,
                'display_order' => 0,
                'icon' => 'fas fa-mobile-alt',
                'is_published' => true,
                'created_by' => 6,
            ],
        ];

        foreach ($announcements as $announcement) {
            \App\Models\Announcement::create($announcement);
        }
    }
}
