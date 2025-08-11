<html>

    <head>

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
            body { font-family: 'Roboto', sans-serif; background: #F8F9FA; }
            .email-body { padding: 35px 25px 76px; }
            .email-center { text-align: center; }
            .email-card { max-width: 700px; background: #FFFFFF; border: solid 1px #DCDCDC; border-top: 5px solid #21B6B0; margin: 0 auto 33px; }
            .email-content { padding: 0 25px 31px; }
            .email-title { color: #242863; font-size: 28px; font-weight: bold; }
            .email-subtitle { color: #242863; font-size: 22px; font-weight: bold; }
            .email-text { color: #242863; font-size: 16px; line-height: 1.5; letter-spacing: normal; }
            .email-disclaimer { color: #495057; font-size: 13px; line-height: 1.5; letter-spacing: normal; font-style: italic; text-align: left;}
            .email-label { color: #7a7a7a; font-size: 16px; letter-spacing: normal; margin-top: 0; margin-bottom: 0; }
            .email-red { color: #DF5E46; }
            .email-blue { color: #21B6B0; }
            .email-button { color: #FFFFFF!important; text-decoration: none; padding: 20px 30px; border-radius: 24px; background-color: #21B6B0; }
            .email-small { color: #7A7A7A; font-size: 13px; }
            .email-black { color: #212529; font-size: 18px; font-weight: bold; }
            .email-black-big { color: #212529; font-size: 22px; font-weight: bold; }
            .email-hr { width: 100%; height: 1px; background-color: #DCDCDC; margin:23px 0; }
            .email-product-place { width: 50%; }
            .email-product { max-width: 330px; margin: 20px auto; }
            .email-product.large { max-width: 500px; margin: 20px auto; }
            .email-product img { object-fit: cover; border-top-left-radius: 16px; border-top-right-radius: 16px; }
            .email-product .info { padding: 13px; border: solid 1px #DCDCDC; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px; }
            .email-product .email-button { display: block; font-size: 14px; text-align: center; padding: 5px 86px; border-radius: 24px; }
            .email-progress { width: 100%; height: 8px; border-radius: 4px; background-color: #DCDCDC; }
            .email-progress .bar { height: 8px; border-radius: 4px; background-color: #242863; }
            .email-big-title { color: #242863; font-size: 40px; font-weight: bold; }
            .email-blurb { width: calc(50% - 30px); text-align: center; padding: 15px 8px 21px; border-radius: 16px; border: solid 1px #21b6b0; background-color: rgba(212, 245, 242, 0.3); }
            .email-blurb img { margin-bottom: 8px; }
            .email-blurb div { color: #242863; font-size: 16px; font-weight: bold; text-decoration: none; }
            .email-table { display: table; width: 100%; }
            .email-footer {background: #f1f1f1}
        </style>

    </head>

    <body>
        <div class="email-body">

            <div class="email-card">
                <div class="email-center" style="margin-bottom:42px;padding:31px 25px 0">
                    <a href="{{ url('/') }}">
                        <img src="{{ url('/assets/logo/head_email.png') }}" class="alignnone size-full wp-image-301" />
                    </a>
                </div>
                <div class="email-content">
                    @yield('content')
                </div>
                <div class="email-footer">
                    <div class="email-center email-center">{{__('emails.layout.companyName') }}, {{ __('emails.layout.address') }}</div>

                    <div class="email-text email-center">{{ __('emails.layout.textPhone') }} <a href="tel:{{ __('emails.layout.phone') }}">{{ __('emails.layout.phoneSpaced') }}</a>
                        <a href="{{ __('emails.layout.web') }}">{{ __('emails.layout.web') }}</a><br><br>
                    </div>
                    <span class="email-disclaimer">{{ __('emails.layout.disclaimerWelcome') }}</span>
                </div>
            </div>
        </div>
    </body>
</html>
