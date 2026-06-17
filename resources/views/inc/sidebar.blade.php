<style>
    :root {
        --primary-blue: #0004FF;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --card-bg: #ffffff;
        --sidebar-width: 300px;
        --top-navbar-height: 66px; 
    }

    /* Fixed backdrop that doesn't mask the top navigation header */
    .sidebar-backdrop {
        position: fixed;
        top: var(--top-navbar-height); 
        left: 0;
        width: 100vw; 
        height: calc(100vh - var(--top-navbar-height));
        background: rgba(15, 23, 42, 0.3);
        backdrop-filter: blur(1px);
        z-index: 1050;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .sidebar-backdrop.active {
        opacity: 1;
        visibility: visible;
    }

    /* Fixed side drawer anchored right below your top navbar */
    .side-popup-bar {
        position: fixed;
        top: var(--top-navbar-height); 
        left: calc(-1 * var(--sidebar-width)); 
        width: var(--sidebar-width);
        height: calc(100vh - var(--top-navbar-height)); 
        background: var(--card-bg);
        box-shadow: 4px 4px 25px rgba(0, 0, 0, 0.05);
        z-index: 1060;
        display: flex;
        flex-direction: column;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: 'Inter', sans-serif;
        border-top: 1px solid var(--border-color);
    }
    .side-popup-bar.open {
        left: 0;
    }

    .sidebar-header {
        padding: 1.25rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .sidebar-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-main);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }
    .sidebar-close {
        background: #f1f5f9;
        border: none;
        width: 30px; height: 30px;
        border-radius: 6px;
        color: var(--text-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }
    .sidebar-close:hover { background: #e2e8f0; color: var(--text-main); }

    .sidebar-body {
        flex-grow: 1;
        overflow-y: auto;
        padding: 1rem 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .nav-link-wrapper { text-decoration: none; display: block; width: 100%; }

    .nav-btn {
        background: #f8fafc;
        border: 1px solid transparent;
        padding: 10px 14px;
        font-weight: 600;
        font-size: 13px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        width: 100%;
        text-align: left;
        transition: 0.2s;
        color: var(--text-main);
        text-decoration: none;
        box-sizing: border-box;
    }
    .nav-btn:hover { background: #f1f5f9; border-color: #cbd5e1; }
    
    .icon-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px; height: 26px;
        border-radius: 6px;
        margin-right: 12px;
        font-size: 12px;
        flex-shrink: 0;
    }

    .nav-btn .chevron {
        margin-left: auto;
        font-size: 11px;
        transition: transform 0.2s;
        color: var(--text-muted);
    }
    .dropdown.active .nav-btn .chevron {
        transform: rotate(180deg);
    }

    .btn-dash { color: #475569; } .btn-dash .icon-box { background: rgba(71, 85, 105, 0.1); }
    .btn-reg { color: #166534; } .btn-reg .icon-box { background: rgba(34, 197, 94, 0.12); }
    .btn-broking { color: #1e40af; } .btn-broking .icon-box { background: rgba(59, 130, 246, 0.12); }
    .btn-insurer { color: #6b21a8; } .btn-insurer .icon-box { background: rgba(168, 85, 247, 0.12); }
    .btn-acc { color: #9a3412; } .btn-acc .icon-box { background: rgba(249, 115, 22, 0.12); }
    .btn-hr { color: #be185d; } .btn-hr .icon-box { background: rgba(236, 72, 153, 0.12); }

    .dropdown { width: 100%; }
    .dropdown-content {
        display: none;
        background: #f8fafc;
        border-radius: 8px;
        margin-top: 4px;
        padding: 4px;
        border: 1px solid var(--border-color);
    }
    .dropdown.active .dropdown-content {
        display: block;
    }
    .dropdown-content a {
        padding: 9px 12px;
        font-size: 13px;
        color: #475569;
        text-decoration: none;
        display: flex;
        align-items: center;
        border-radius: 6px;
        font-weight: 500;
    }
    .dropdown-content a:hover { background: #e2e8f0; color: var(--primary-blue); }
    .dropdown-content a i { width: 18px; margin-right: 12px; text-align: center; font-size: 12px; }

    .sidebar-footer {
        padding: 1.25rem;
        border-top: 1px solid var(--border-color);
        background: #f8fafc;
        margin-top: auto;
    }
    .nav-profile-group {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }
    .nav-user-text { text-align: left; line-height: 1.4; }
    .nav-user-text h4 { font-size: 13px; font-weight: 700; margin: 0; text-transform: uppercase; color: var(--text-main); }
    .nav-user-text .pos { font-size: 11px; font-weight: 600; margin: 0; color: var(--text-muted); }

    .initials-avatar {
        width: 40px; height: 40px;
        background: var(--primary-blue);
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px var(--border-color);
    }
    
    #userDD {
        bottom: 75px;
        left: 1.25rem;
        right: 1.25rem;
        top: auto;
        position: absolute;
        min-width: calc(var(--sidebar-width) - 2.5rem);
    }
</style>

<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar(false)"></div>

<nav class="side-popup-bar" id="sidePopupBar">
    <div class="sidebar-header">
        <h3 class="sidebar-title">System Menu</h3>
        <button class="sidebar-close" onclick="toggleSidebar(false)">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="sidebar-body">
        <a href="{{ url('/insurance_broking/dashboard') }}" class="nav-link-wrapper">
            <button class="nav-btn btn-dash">
                <span class="icon-box"><i class="fas fa-chart-pie"></i></span> Dashboard
            </button>
        </a>

        <div class="dropdown" id="regDropdownContainer">
            <button onclick="toggleDropdown('regDD', 'regDropdownContainer')" class="nav-btn btn-reg">
                <span class="icon-box"><i class="fas fa-plus-circle"></i></span> Registration
                <i class="fas fa-chevron-down chevron"></i>
            </button>
            <div id="regDD" class="dropdown-content">
                <a href="{{ route('insurance_broking.register', ['action' => 'register_client']) }}"><i class="fas fa-user-plus"></i> Client</a>
                <a href="{{ route('insurance_broking.register', ['action' => 'register_policy']) }}"><i class="fas fa-file-alt"></i> Policy</a>
                <a href="{{ route('insurance_broking.register', ['action' => 'register_slip']) }}"><i class="fas fa-clipboard-list"></i> Placing Slip</a>
                <a href="{{ route('insurance_broking.register', ['action' => 'register_vehicle']) }}"><i class="fas fa-car"></i> Motor Vehicle</a>
                <a href="{{ route('insurance_broking.register', ['action' => 'register_claim']) }}"><i class="fas fa-exclamation-triangle" style="color:#ef4444"></i> Claim</a>
            </div>
        </div>

        <div class="dropdown" id="devDropdownContainer">
            <button onclick="toggleDropdown('devDD', 'devDropdownContainer')" class="nav-btn btn-broking">
                <span class="icon-box"><i class="fas fa-file-contract"></i></span> Development
                <i class="fas fa-chevron-down chevron"></i>
            </button>
            <div id="devDD" class="dropdown-content">
                <a href="{{ route('insurance_broking.quotations.create', ['action' => 'register_potential_client']) }}"><i class="fas fa-user-plus"></i> Potential Client</a>
                <a href="{{ route('insurance_broking.quotations.create', ['action' => 'register_quote']) }}"><i class="fas fa-file-invoice-dollar"></i> Generate Quotation</a>
                <a href="#"><i class="fas fa-file-contract"></i> Broking Slip</a>
            </div>
        </div>

        <div class="dropdown" id="insDropdownContainer">
            <button onclick="toggleDropdown('insDD', 'insDropdownContainer')" class="nav-btn btn-insurer">
                <span class="icon-box"><i class="fas fa-building-columns"></i></span> Insurers
                <i class="fas fa-chevron-down chevron"></i>
            </button>
            <div id="insDD" class="dropdown-content">
                <a href="{{ route('insurance_broking.register', ['action' => 'register_insurer']) }}"><i class="fas fa-university"></i> Insurer Registry</a>
                <a href="{{ url('/insurance_broking/accounts/payment_voucher') }}"><i class="fas fa-money-check-alt"></i> Payment Vouchers</a>
                <a href="{{ url('/insurance_broking/accounts/remittance') }}"><i class="fas fa-list-check"></i> Remittance Schedules</a>
            </div>
        </div>

        <div class="dropdown" id="hrDropdownContainer">
            <button onclick="toggleDropdown('hrDD', 'hrDropdownContainer')" class="nav-btn btn-hr">
                <span class="icon-box"><i class="fas fa-users-gear"></i></span> HR
                <i class="fas fa-chevron-down chevron"></i>
            </button>
            <div id="hrDD" class="dropdown-content">
                <a href="{{ url('/hr/employee_registration') }}"><i class="fas fa-user-plus"></i> Employee Registration</a>
                <a href="#"><i class="fas fa-user-tie"></i> Employee Directory</a>
                <a href="#"><i class="fas fa-calendar-check"></i> Leave Management</a>
                
                @if(Auth::check() && in_array(Auth::user()->role ?? session('role'), ['Admin', 'Accountant']))
                    <a href="{{ url('/hr/payroll') }}"><i class="fas fa-file-signature"></i> Payroll</a>
                @endif
                
                <a href="#"><i class="fas fa-briefcase"></i> Recruitment</a>
            </div>
        </div>

        <div class="dropdown" id="accDropdownContainer">
            <button onclick="toggleDropdown('accDD', 'accDropdownContainer')" class="nav-btn btn-acc">
                <span class="icon-box"><i class="fas fa-calculator"></i></span> Finance
                <i class="fas fa-chevron-down chevron"></i>
            </button>
            <div id="accDD" class="dropdown-content">
                <a href="{{ route('insurance_broking.accounts.invoices.generate_invoice') }}"><i class="fas fa-file-invoice"></i> Smart Invoice</a>
                <a href="{{ route('insurance_broking.accounts.credit_notes.generate') }}"><i class="fas fa-minus-square"></i> Credit Note</a>
                <a href="{{ route('insurance_broking.accounts.payment_vouchers.show') }}"><i class="fas fa-file-signature"></i> Payment Voucher</a>
                <a href="{{ route('insurance_broking.accounts.debtors.index') }}"><i class="fas fa-hand-holding-usd"></i> Debtors</a>
                <a href="{{ route('insurance_broking.accounts.client_statements.index') }}"><i class="fas fa-file-invoice-dollar"></i> Statement</a>
                <a href="{{ route('insurance_broking.accounts.receipts.show') }}"><i class="fas fa-receipt"></i> Payment Receipt</a>
            </div>
        </div>                  
    </div>

    <div class="sidebar-footer">
        <div class="nav-profile-group">
            <div class="nav-user-text">
                <h4>{{ Auth::check() ? Auth::user()->first_name : 'System' }}</h4>
                <p class="pos">{{ Auth::check() ? Auth::user()->role : 'User' }}</p>
            </div>
            
            <div class="dropdown" id="userDropdownContainer">
                <div class="initials-avatar" onclick="toggleDropdown('userDD', 'userDropdownContainer')">
                    @php
                        $displayName = Auth::check() ? Auth::user()->first_name : 'System User';
                        $firstLetter = mb_substr($displayName, 0, 1, 'UTF-8');
                    @endphp
                    {{ strtoupper($firstLetter) }}
                </div>
                <div id="userDD" class="dropdown-content">
                    <div style="padding:10px; border-bottom:1px solid #eee; text-align:center;">
                        <strong style="font-size:13px; display:block;">{{ Auth::check() ? Auth::user()->first_name : 'System' }}</strong>
                        <small class="text-muted" style="font-size: 0.7rem;">{{ Auth::check() ? Auth::user()->role : 'User' }}</small>
                    </div>
                    
                    @if(Route::has('account.edit'))
                        <a href="{{ route('account.edit') }}"><i class="fas fa-user-circle"></i> Profile</a>
                    @endif
                    
                    @if(Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="#" onclick="event.preventDefault(); this.closest('form').submit();" style="color:#ef4444">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    function toggleSidebar(shouldOpen) {
        const sidebar = document.getElementById('sidePopupBar');
        const backdrop = document.getElementById('sidebarBackdrop');
        
        if (sidebar && backdrop) {
            if (shouldOpen) {
                sidebar.classList.add('open');
                backdrop.classList.add('active');
            } else {
                sidebar.classList.remove('open');
                backdrop.classList.remove('active');
                document.querySelectorAll(".dropdown").forEach(el => el.classList.remove('active'));
            }
        }
    }

    function toggleDropdown(menuId, containerId) {
        if (window.event) window.event.stopPropagation();
        
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const wasOpen = container.classList.contains('active');
        
        document.querySelectorAll(".dropdown").forEach(el => {
            if(el.id !== containerId && el.id !== 'userDropdownContainer') {
                el.classList.remove('active');
            }
        });
        
        if (wasOpen) {
            container.classList.remove('active');
        } else {
            container.classList.add('active');
        }
    }

    window.onclick = function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll(".dropdown").forEach(el => el.classList.remove('active'));
        }
    }
</script>