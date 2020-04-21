<?php // $sum = 0; ?>
<div class="list-group">
	{{-- <div class="list-group-item text-center @if(!App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->show_powered_by_estatebaron) hide @endif"> --}}
		{{-- @if($user->profile_picture) --}}
		{{-- <img src="{{asset($user->profile_picture)}}" height="100" style="border-radius: 3px;"> --}}
		{{-- @else --}}
		{{-- <a href="https://konkrete.io" target="_blank" ><img src="{{ asset('/assets/images/konkrete_logo_dark.png') }}" width="100px" style="border-radius: 3px;"></a> --}}
		{{-- @endif --}}
		{{-- <a href="https://konkrete.io" target="_blank" ><h3 class="text-center"><b>KONKRETE Balance</b></h3></a> --}}
		{{-- @foreach($user->credits as $credit) --}}
			<?php //$sum = $sum + $credit->amount; ?>
		{{-- @endforeach --}}
		{{-- <a href="https://konkrete.io" target="_blank" ><h4><b>{{$sum}}</b></h4></a> --}}
	{{-- </div> --}}
	<a href="{{route('users.show', [$user])}}" class="list-group-item @if($active == 1) active @endif">Profile </a>
	<a href="{{route('users.referral', [$user])}}" class="list-group-item @if($active == 11) active @endif">Referral </a>
	<a href="{{route('home')}}#projects" class="list-group-item @if($active == 7) active @endif @if(!App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->show_powered_by_estatebaron) hide @endif">All Projects</a>
	{{--<a href="{{route('users.invitation', [$user])}}" class="list-group-item @if($active == 6) active @endif">Invite friends </a>--}}
	@if($user->invite_only_projects->count())
	<a href="{{route('projects.invite.only')}}" class="list-group-item @if($active == 8) active @endif">Invite for Projects </a>
	@endif
	{{--<a href="{{route('users.interests', [$user])}}" class="list-group-item @if($active == 2) active @endif">Interest Expressed </a>--}}
	<a href="{{route('users.document', [$user])}}" class="list-group-item @if($active == 10) active @endif">KYC @if(($user->idDoc && $user->idDoc->verified == '1') || ($user->digitalIdKyc)) <i class="fa fa-check-circle" aria-hidden="true"></i>
 	@endif </a>
	<!-- {{-- @if($user->verify_id != 2)<a href="{{route('users.verification', [$user])}}" class="list-group-item @if($active == 3) active @endif">Verification </a> @endif --}}
	@if($user->verify_id != 2)<a href="<?php echo url();?>/users/{{$user->id}}/verification" class="list-group-item @if($active == 3) active @endif" target="_blank">Verification </a> @endif -->
	{{--<a href="{{route('users.book', [$user])}}" class="list-group-item @if($active == 4) active @endif">Book a Meeting </a>--}}
	<?php $roles = $user->roles; ?>
	@if($roles->contains('role', 'developer'))
	<a href="{{route('users.submit', [$user])}}" class="list-group-item @if($active == 5) active @endif">Submit a Project </a>
	@endif
	<a href="{{route('users.investments', [$user])}}" class="list-group-item @if($active == 6) active @endif">Investments </a>
	{{-- <a href="{{ route('users.redemptions', [$user]) }}" class="list-group-item @if($active == 12) active @endif">Redemption Requests </a> --}}
	<a href="{{route('users.notifications', [$user])}}" class="list-group-item @if($active == 9) active @endif">Notifications </a>
</div>
