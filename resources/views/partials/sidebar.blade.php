<div class="list-group sidebar-nav">
	<a href="{{route('users.show', [$user])}}" class="list-group-item @if($active == 1) active @endif"><i class="fa fa-user" aria-hidden="true"></i> Profile </a>
	<a href="{{route('home')}}#projects" class="list-group-item @if($active == 7) active @endif @if(!App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->show_powered_by_estatebaron) hide @endif"><i class="fa fa-th-list"></i> All Projects</a>
	@if($user->invite_only_projects->count())
	<a href="{{route('projects.invite.only')}}" class="list-group-item @if($active == 8) active @endif"><i class="fa fa-group"></i> Invite for Projects </a>
	@endif
	<a href="{{route('users.document', [$user])}}" class="list-group-item @if($active == 10) active @endif"><i class="fa fa-credit-card" aria-hidden="true"></i> KYC @if(($user->idDoc && $user->idDoc->verified == '1') || ($user->digitalIdKyc)) <i class="fa fa-check-circle" aria-hidden="true"></i>
 	@endif </a>
	<?php $roles = $user->roles; ?>
	@if($roles->contains('role', 'developer'))
		<a href="{{route('users.submit', [$user])}}" class="list-group-item @if($active == 5) active @endif"><i class="fa fa-cloud-upload"></i> Submit a Project </a>
	@endif
	<a href="{{route('users.investments', [$user])}}" class="list-group-item @if($active == 6) active @endif"><i class="fa fa-bar-chart" aria-hidden="true"></i> Investments </a>
	<a href="{{route('users.notifications', [$user])}}" class="list-group-item @if($active == 9) active @endif"><i class="fa fa-envelope"></i> Notifications </a>
</div>
