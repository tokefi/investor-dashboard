{{-- <div class="list-group">
	<div class="list-group-item"><img src="{{asset('assets/images/default-male.png')}}" width="140" class="img-circle"></div>
	<a href="{{route('dashboard.index')}}" class="list-group-item @if($active == 1) active @endif">Dashboard <i class="fa fa-tachometer pull-right"></i></a>
	<a href="{{route('dashboard.users')}}" class="list-group-item @if($active == 2) active @endif">Users <i class="fa fa-users pull-right"></i></a>
	<a href="{{route('dashboard.projects')}}" class="list-group-item @if($active == 3) active @endif">Projects <i class="fa fa-paperclip pull-right"></i></a>
	<a href="{{route('dashboard.kyc')}}" class="list-group-item @if($active == 9) active @endif">KYC Requests <i class="fa fa-file pull-right"></i></a>
	<a href="{{route('dashboard.configurations')}}" class="list-group-item @if($active == 4) active @endif">Configurations <i class="fa fa-edit pull-right"></i></a>
	<a href="{{route('dashboard.broadcastMail')}}" class="list-group-item @if($active == 5) active @endif">Broadcast <i class="fa fa-envelope pull-right"></i></a>
	<a href="{{route('dashboard.import.contacts')}}" class="list-group-item @if($active == 10) active @endif">Import Users <i class="fa fa-user-plus pull-right"></i></a>
	<a href="{{route('dashboard.investmentRequests')}}" class="list-group-item @if($active == 6) active @endif">Requests<i class="fa fa-comments-o pull-right"></i></a>
	<a href="{{route('dashboard.prospectus.downloads')}}" class="list-group-item @if($active == 7) active @endif">Prospectus Downloads<i class="fa fa-download pull-right"></i></a>
	<a href="{{ route('dashboard.redemption.requests') }}" class="list-group-item @if($active == 11) active @endif">Redemption Requests<i class="fa fa-comments pull-right"></i></a>
	<a href="https://docs.google.com/document/d/1MvceKeyqd93GmjXBSa4r0Y9rJOKfJq38VNk4smPr3l8/edit#heading=h.mgf45ju607e6" target="_blank" class="list-group-item @if($active == 8) active @endif">FAQ Help<i class="fa fa-info-circle pull-right"></i></a>
</div> --}}

<div class="list-group sidebar-nav">
	<div class="list-group-item"><img src="{{asset('assets/images/default-male.png')}}" width="40" class="img-circle"><div>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}<br>Admin</div></div>
	<a href="{{route('dashboard.index')}}" class="list-group-item @if($active == 1) active @endif"><img src="{{asset('assets/images/new-ui/dashboard/sidebar/dashboard.png')}}" alt=""> Dashboard</a>
	<a href="{{route('dashboard.users')}}" class="list-group-item @if($active == 2) active @endif"><img src="{{asset('assets/images/new-ui/dashboard/sidebar/users.png')}}" alt=""> Users</i></a>
	<a href="{{route('dashboard.projects')}}" class="list-group-item @if($active == 3) active @endif"><img src="{{asset('assets/images/new-ui/dashboard/sidebar/projects.png')}}" alt=""> Projects</a>
	{{-- <a href="{{route('dashboard.kyc')}}" class="list-group-item @if($active == 9) active @endif"><img src="{{asset('assets/images/new-ui/dashboard/sidebar/dashboard.png')}}" alt=""> KYC Requests</a> --}}
	<a href="{{route('dashboard.configurations')}}" class="list-group-item @if($active == 4) active @endif"><img src="{{asset('assets/images/new-ui/dashboard/sidebar/dashboard.png')}}" alt=""> Settings</a>
	<a href="{{route('dashboard.configurations')}}" class="list-group-item @if($active == 4) active @endif"><img src="{{asset('assets/images/new-ui/dashboard/sidebar/broadcast.png')}}" alt=""> Broadcast</a>
	{{--<a href="{{route('dashboard.broadcastMail')}}" class="list-group-item @if($active == 5) active @endif">Broadcast <i class="fa fa-envelope pull-right"></i></a>--}}
	<a href="{{route('dashboard.import.contacts')}}" class="list-group-item @if($active == 10) active @endif"><img src="{{asset('assets/images/new-ui/dashboard/sidebar/import_users.png')}}" alt=""> Import Users</a>
	<a href="{{route('dashboard.investmentRequests')}}" class="list-group-item @if($active == 6) active @endif"><img src="{{asset('assets/images/new-ui/dashboard/sidebar/requests.png')}}" alt=""> Requests</a>
	<a href="{{route('dashboard.investmentRequests')}}" class="list-group-item @if($active == 6) active @endif"><img src="{{asset('assets/images/new-ui/dashboard/sidebar/faq_help.png')}}" alt=""> FAQ Help</a>
	<a href="{{route('dashboard.prospectus.downloads')}}" class="list-group-item @if($active == 7) active @endif"><img src="{{asset('assets/images/new-ui/dashboard/sidebar/prospectus_download.png')}}" alt=""> Prospectus Download</a>
	{{-- <a href="{{ route('dashboard.redemption.requests') }}" class="list-group-item @if($active == 11) active @endif">Redemption Requests<i class="fa fa-comments pull-right"></i></a> --}}
	<a href="https://docs.google.com/document/d/1MvceKeyqd93GmjXBSa4r0Y9rJOKfJq38VNk4smPr3l8/edit#heading=h.mgf45ju607e6" target="_blank" class="list-group-item @if($active == 8) active @endif"><img src="{{asset('assets/images/new-ui/dashboard/sidebar/faq_help.png')}}" alt=""> FAQ Help</a>
</div>

