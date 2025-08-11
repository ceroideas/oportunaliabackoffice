
<h3>Formulario de contacto Oportunalia</h3>
<div style="color: rgb(0,0,0)">
	{{-- v1
    {{ __('emails.contact.firstname') }}: {{ $data['firstname'] }}
    --}}
    {{--v2--}}
    {{ __('emails.contact.firstname') }}: {{ $firstname }}
</div>

<br>

<div style="color: rgb(0,0,0)">
	{{ __('emails.contact.lastname') }}: {{ $lastname }}
</div>

<br>

<div style="color: rgb(0,0,0)">
	{{ __('emails.contact.email') }}: {{ $email }}
</div>

<br>

<div style="color: rgb(0,0,0)">
	{{ __('emails.contact.phone') }}: {{ $phone }}
</div>

<br>

<div style="color: rgb(0,0,0)">
	{{ __('emails.contact.subject') }}: {{ $subject }}
</div>

<br>

<div style="color: rgb(0,0,0)">
	{{ __('emails.contact.message') }}: {{ $message }}
</div>
