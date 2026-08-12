@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Header Section -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>Welcome back, <span class="user-name">{{ auth()->user()->name }}</span>! 👋</h1>
            <p class="date-time" id="datetime"></p>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.request.create') }}" class="btn-primary">+ New Request</a>
        </div>
    </div>


    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon yellow">🟡</div>
            <div class="stat-content">
                <h3>Pending</h3>
                <p class="stat-number">{{ $pendingRequests }}</p>
                <p class="stat-label">Waiting for review</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon blue">🔵</div>
            <div class="stat-content">
                <h3>Processing</h3>
                <p class="stat-number">{{ $processingRequests }}</p>
                <p class="stat-label">Being prepared</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">🟢</div>
            <div class="stat-content">
                <h3>Approved</h3>
                <p class="stat-number">{{ $approvedRequests }}</p>
                <p class="stat-label">Ready for pickup</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red">🔴</div>
            <div class="stat-content">
                <h3>Rejected</h3>
                <p class="stat-number">{{ $rejectedRequests }}</p>
                <p class="stat-label">With reasons</p>
            </div>
        </div>
    </div>

    <!-- Status Legend Card -->
    <div class="card" style="margin-bottom: 30px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-left: 4px solid #0284c7;">
        <div class="card-header">
            <h2 style="color: #0c4a6e;">📖 Understanding Request Status</h2>
        </div>
        <div style="padding: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <span style="font-size: 1.5em;">🟡</span>
                <div>
                    <strong style="color: #92400e;">Pending</strong>
                    <p style="font-size: 0.9em; color: #6b5b2e; margin: 4px 0;">Waiting for admin review</p>
                </div>
            </div>
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <span style="font-size: 1.5em;">🔵</span>
                <div>
                    <strong style="color: #1e40af;">Processing</strong>
                    <p style="font-size: 0.9em; color: #1e3a8a; margin: 4px 0;">Document being prepared</p>
                </div>
            </div>
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <span style="font-size: 1.5em;">🟢</span>
                <div>
                    <strong style="color: #166534;">Approved</strong>
                    <p style="font-size: 0.9em; color: #15803d; margin: 4px 0;">Ready for pickup/download</p>
                </div>
            </div>
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <span style="font-size: 1.5em;">🔴</span>
                <div>
                    <strong style="color: #991b1b;">Rejected</strong>
                    <p style="font-size: 0.9em; color: #7c2d12; margin: 4px 0;">See notes for reason</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Recent Requests -->
        <div class="card large">
            <div class="card-header">
                <h2>📋 Recent Requests</h2>
                <a href="{{ route('resident.requests') }}" class="view-all">View All →</a>
            </div>
            <div class="table-wrapper">
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Status</th>
                            <th>Date Requested</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($recentRequests->count() > 0)
                            @foreach($recentRequests as $request)
                            <tr>
                                <td>
                                    <span class="doc-icon">📄</span>
                                    <a href="{{ route('resident.request.show', $request) }}" style="color: #667eea; font-weight: 600;">
                                        {{ ucfirst(str_replace('_', ' ', $request->document_type)) }}
                                    </a>
                                </td>
                                <td>
                                    @php
                                        $status = $request->status ?? 'pending';
                                        $statusConfig = match($status) {
                                            'pending' => [
                                                'emoji' => '🟡',
                                                'label' => 'Pending',
                                                'description' => 'waiting for review',
                                                'class' => 'badge-pending'
                                            ],
                                            'processing' => [
                                                'emoji' => '🔵',
                                                'label' => 'Processing',
                                                'description' => 'being prepared',
                                                'class' => 'badge-processing'
                                            ],
                                            'approved' => [
                                                'emoji' => '🟢',
                                                'label' => 'Approved',
                                                'description' => 'ready for pickup',
                                                'class' => 'badge-approved'
                                            ],
                                            'rejected' => [
                                                'emoji' => '🔴',
                                                'label' => 'Rejected',
                                                'description' => 'see notes for reason',
                                                'class' => 'badge-danger'
                                            ],
                                            'completed' => [
                                                'emoji' => '✅',
                                                'label' => 'Completed',
                                                'description' => 'document obtained',
                                                'class' => 'badge-completed'
                                            ],
                                            default => [
                                                'emoji' => 'ℹ️',
                                                'label' => ucfirst($status),
                                                'description' => 'in progress',
                                                'class' => 'badge-info'
                                            ]
                                        };
                                    @endphp
                                    <div class="badge {{ $statusConfig['class'] }}" style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px; width: fit-content;">
                                        <span style="font-size: 1.2em;">{{ $statusConfig['emoji'] }}</span>
                                        <div style="text-align: left;">
                                            <div style="font-weight: 600; font-size: 0.9em;">{{ $statusConfig['label'] }}</div>
                                            <div style="font-size: 0.75em; opacity: 0.8;">{{ $statusConfig['description'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $request->created_at->format('M d, Y') }}</td>
                                <td><a href="{{ route('resident.request.show', $request) }}" class="action-link">View</a></td>
                            </tr>
                            @endforeach
                        @else
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px; color: #7f8c8d;">
                                No requests yet. <a href="{{ route('resident.request.create') }}" style="color: #667eea; text-decoration: none; font-weight: 600;">Create one now</a>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h2>⚡ Quick Actions</h2>
            </div>
            <div class="quick-actions">
                <a href="{{ route('resident.request.create') }}" class="action-btn">
                    <span class="action-icon">📝</span>
                    <span>New Request</span>
                </a>
                <a href="{{ route('resident.documents') }}" class="action-btn">
                    <span class="action-icon">📚</span>
                    <span>My Documents</span>
                </a>
                <a href="{{ route('resident.announcements') }}" class="action-btn">
                    <span class="action-icon">📢</span>
                    <span>Announcements</span>
                </a>
                <a href="{{ route('resident.profile') }}" class="action-btn">
                    <span class="action-icon">👤</span>
                    <span>Edit Profile</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="content-grid">
        <!-- Latest Announcements -->
        <div class="card">
            <div class="card-header">
                <h2>📢 Latest Announcements</h2>
                @if($announcements->count() > 0)
                <a href="{{ route('resident.announcements') }}" class="view-all">View All →</a>
                @endif
            </div>
            <div class="announcements-list">
                @if($announcements->count() > 0)
                    @foreach($announcements as $announcement)
                    <div class="announcement-item {{ !$loop->first ? 'border-top' : '' }}">
                        <div class="announcement-date">{{ $announcement->created_at->diffForHumans() }}</div>
                        <a href="{{ route('resident.announcement.show', $announcement) }}" style="text-decoration: none; color: inherit;">
                            <h4>{{ $announcement->title }}</h4>
                            <p>{{ Str::limit($announcement->content, 100) }}</p>
                        </a>
                    </div>
                    @endforeach
                @else
                <div style="text-align: center; padding: 30px; color: #7f8c8d;">
                    <p>No announcements available yet.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card">
            <div class="card-header">
                <h2>📅 Quick Stats</h2>
            </div>
            <div class="timeline">
                @if($totalRequests > 0)
                <div class="timeline-item">
                    <div class="timeline-marker success"></div>
                    <div class="timeline-content">
                        <h4>Total Requests Submitted</h4>
                        <p>You have submitted {{ $totalRequests }} document request{{ $totalRequests > 1 ? 's' : '' }}</p>
                    </div>
                </div>
                @endif
                
                @if($approvedRequests > 0)
                <div class="timeline-item">
                    <div class="timeline-marker info"></div>
                    <div class="timeline-content">
                        <h4>Approved Requests 🟢</h4>
                        <p>{{ $approvedRequests }} of your requests have been approved and are ready for pickup or download</p>
                    </div>
                </div>
                @endif
                
                @if($pendingRequests > 0)
                <div class="timeline-item">
                    <div class="timeline-marker warning"></div>
                    <div class="timeline-content">
                        <h4>Pending Requests 🟡</h4>
                        <p>You have {{ $pendingRequests }} request{{ $pendingRequests > 1 ? 's' : '' }} still waiting for admin review</p>
                    </div>
                </div>
                @endif

                @if($completedRequests > 0)
                <div class="timeline-item">
                    <div class="timeline-marker success"></div>
                    <div class="timeline-content">
                        <h4>Completed Requests ✅</h4>
                        <p>{{ $completedRequests }} request{{ $completedRequests > 1 ? 's' : '' }} have been completed this month</p>
                    </div>
                </div>
                @endif

                @if($totalRequests == 0)
                <div style="text-align: center; padding: 30px; color: #7f8c8d;">
                    <p>📭 You haven't submitted any requests yet. <a href="{{ route('resident.request.create') }}" style="color: #667eea; text-decoration: none; font-weight: 600;">Create one now</a></p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    // Update date and time
    function updateDateTime() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        document.getElementById('datetime').textContent = now.toLocaleDateString('en-US', options);
    }
    updateDateTime();
    setInterval(updateDateTime, 60000);

    // Per-user key for pickup banner dismissals
    const PICKUP_KEY = 'pickupBannerDismissed_{{ auth()->id() }}';

    // Hide pickup banner on load if all documents in it were already dismissed
    document.addEventListener('DOMContentLoaded', function () {
        const banner = document.querySelector('.ready-for-pickup-banner');
        if (!banner) return;

        const idsAttr = banner.getAttribute('data-request-ids') || '';
        const currentIds = idsAttr.split(',').filter(Boolean);
        if (currentIds.length === 0) return;

        let dismissedIds = [];
        try {
            dismissedIds = JSON.parse(localStorage.getItem(PICKUP_KEY)) || [];
        } catch (e) {
            dismissedIds = [];
        }

        const hasNew = currentIds.some(id => !dismissedIds.includes(id));
        if (!hasNew) {
            banner.remove();
        }
    });

    // Dismiss Ready for Pickup Banner and remember dismissed document IDs
    function dismissPickupBanner() {
        const banner = document.querySelector('.ready-for-pickup-banner');
        if (!banner) return;

        const idsAttr = banner.getAttribute('data-request-ids') || '';
        const currentIds = idsAttr.split(',').filter(Boolean);

        banner.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => {
            banner.remove();

            let dismissedIds = [];
            try {
                dismissedIds = JSON.parse(localStorage.getItem(PICKUP_KEY)) || [];
            } catch (e) {
                dismissedIds = [];
            }

            const updated = Array.from(new Set([...dismissedIds, ...currentIds]));
            localStorage.setItem(PICKUP_KEY, JSON.stringify(updated));
        }, 300);
    }

    // Handle notification mark as read
    document.addEventListener('DOMContentLoaded', function() {
        const markReadButtons = document.querySelectorAll('button[onclick*="markNotificationRead"]');
        markReadButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const notificationId = this.getAttribute('data-notification-id');
                if (notificationId) {
                    markNotificationRead(notificationId, this);
                }
            });
        });
    });

    function markNotificationRead(notificationId, buttonElement) {
        fetch(`/resident/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the notification item from both dropdown and main alerts
                const dropdownItem = buttonElement.closest('.dropdown-item');
                const alertItem = buttonElement.closest('.alert-item');

                if (dropdownItem) {
                    dropdownItem.style.animation = 'fadeOut 0.3s ease forwards';
                    setTimeout(() => {
                        dropdownItem.remove();
                        updateNotificationCount();
                    }, 300);
                }

                if (alertItem) {
                    alertItem.style.animation = 'fadeOut 0.3s ease forwards';
                    setTimeout(() => {
                        alertItem.remove();
                        updateNotificationCount();
                    }, 300);
                }
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    function deleteNotification(notificationId, buttonElement) {
        if (!confirm('Delete this notification?')) {
            return;
        }

        fetch(`/resident/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const dropdownItem = buttonElement.closest('.dropdown-item');
                const alertItem = buttonElement.closest('.alert-item');

                if (dropdownItem) {
                    dropdownItem.style.animation = 'fadeOut 0.3s ease forwards';
                    setTimeout(() => {
                        dropdownItem.remove();
                        updateNotificationCount();
                    }, 300);
                }

                if (alertItem) {
                    alertItem.style.animation = 'fadeOut 0.3s ease forwards';
                    setTimeout(() => {
                        alertItem.remove();
                        updateNotificationCount();
                    }, 300);
                }
            }
        })
        .catch(error => {
            console.error('Error deleting notification:', error);
        });
    }

    function updateNotificationCount() {
        // Count unread notifications from both dropdown and main alerts
        const dropdownItems = document.querySelectorAll('#notificationDropdown .dropdown-item.unread');
        const alertItems = document.querySelectorAll('.alerts-list .alert-item');
        const badge = document.querySelector('.notification-badge');
        const alertsSection = document.querySelector('#alerts-section');
        
        // Count notifications (excluding ready for pickup alerts)
        let notificationCount = 0;
        alertItems.forEach(item => {
            if (!item.classList.contains('alert-ready-pickup')) {
                notificationCount++;
            }
        });
        
        // Add dropdown unread count
        notificationCount += dropdownItems.length;
        
        // Check if ready for pickup exists
        const readyPickupExists = document.querySelectorAll('.dropdown-ready-pickup, .alert-ready-pickup').length > 0;
        
        // Update badge
        const totalCount = notificationCount + (readyPickupExists ? 1 : 0);
        if (badge) {
            if (totalCount > 0) {
                badge.textContent = totalCount;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
        
        // Hide alerts section if no notifications left
        if (notificationCount === 0 && !readyPickupExists) {
            if (alertsSection) {
                alertsSection.style.display = 'none';
            }
        }
    }

</script>

<style>
    .alerts-title {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alerts-title-text {
        font-weight: 600;
        color: #111827;
    }

    .alerts-count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        border-radius: 999px;
        background: #ef4444;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .alert-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 4px;
    }

    .resident-notification-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 4px;
        border-radius: 999px;
        background: #3b82f6;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
    }

    .alert-message {
        margin-bottom: 4px;
        color: #4b5563;
    }

    .alert-meta {
        color: #9ca3af;
        font-size: 0.8rem;
    }

    .notification-bell {
        position: relative;
        border: none;
        background: transparent;
        cursor: pointer;
        margin-right: 12px;
        padding: 8px;
        border-radius: 999px;
        transition: background 0.2s ease;
    }

    .notification-bell:hover {
        background: rgba(102, 126, 234, 0.08);
    }

    .bell-icon {
        font-size: 1.3rem;
    }

    .notification-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        min-width: 18px;
        height: 18px;
        padding: 0 4px;
        border-radius: 999px;
        background: #ef4444;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 0 2px #fff;
    }


    @keyframes slideOut {
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
    
    @keyframes fadeOut {
        to {
            opacity: 0;
        }
    }

    /* Status Badge Styles */
    .badge {
        font-size: 0.85em;
        font-weight: 600;
    }

    .badge-pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border: 1px solid #fcd34d;
    }

    .badge-processing {
        background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
        color: #1e40af;
        border: 1px solid #60a5fa;
    }

    .badge-approved {
        background: linear-gradient(135deg, #bbf7d0 0%, #86efac 100%);
        color: #166534;
        border: 1px solid #4ade80;
    }

    .badge-danger {
        background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
        color: #991b1b;
        border: 1px solid #f87171;
    }

    .badge-completed {
        background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%);
        color: #1f2937;
        border: 1px solid #6b7280;
    }

    .badge-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        border: 1px solid #60a5fa;
    }
</style>
@endsection
