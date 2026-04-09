@extends('layouts.app')

@section('css')
<style>
#social-networks-wrap {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
    margin-top: 40px;
}

#social-networks-wrap a {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    width: 120px;
    font-size: 14px;
    font-weight: 600;
    padding: 15px;
    border-radius: 10px;
    text-decoration: none;
    color: #000;
    transition: all 0.3s;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
    background: #fff;
}

#social-networks-wrap a img {
    max-width: 75px;
    margin-bottom: 10px;
    border-radius: 50%;
    box-shadow: 0px 0px 3px 0px #888;
}

#social-networks-wrap.disabled a img {
    filter: grayscale(100%);
    pointer-events: none;
}

#social-networks-wrap.disabled {
    pointer-events: none;
    opacity: 0.6;
}
</style>
@endsection

@section('page-header')
<div class="page-header mt-5">
    <div class="page-leftheader">
        <h4 class="page-title mb-0">{{ $page_title }}</h4>
        <ol class="breadcrumb mb-2">
            <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">{{ __('User') }}</a></li>
            <li class="breadcrumb-item active">{{ $page_title }}</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h4 class="text-center my-4">Blast Your Link to 100+ Free Traffic Sources</h4>

            <div class="row justify-content-center" id="input-box">
                <div class="col-md-8">
                    <!-- <label for="page">Your URL</label> -->
                    <div class="card mb-4 border-0 image-prompt-wrapper">
                        <div class="card-body p-0">					
                            <div class="image-prompt d-flex">
                                <div class="input-box mb-0">								
                                    <div class="form-group">							    
                                        <input type="text" class="form-control" id="page" name="page" placeholder="https://www.xyz.com" required="" style="background-color: inherit !important;border-right: none;">
                                    </div> 
                                </div> 
                                <div>
                                    <button type="button" name="submit" class="btn btn-primary w-100 pt-2 pb-2 apply_url" id="image-generate"><i class="fa-sharp fa-solid fa-bolt mr-2"></i>Unlock Links</button>
                                </div>
                            </div>					
                        </div>
                    </div>
                </div>
            </div>

            <div id="social-networks-wrap" class="disabled">
                @foreach($social as $soc)
                    <a class="share-btn" target="_blank"
                       href="{{ $soc->name === 'Print' ? '#' : $soc->url }}"
                       @if($soc->name === 'Print') onclick="window.print(); return false;" @endif>
                        <img src="{{ $soc->logo }}" alt="{{ $soc->name }}">
                        <span>{{ $soc->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(function () {
        $('.apply_url').click(function () {
            var target = $('#page').val().trim();
            if (target) {
                $('#input-box').fadeOut();
                $('#social-networks-wrap').removeClass('disabled');
                $('.share-btn').each(function () {
                    let href = $(this).attr('href');
                    if (href.includes('#URL')) {
                        $(this).attr('href', href.replace('#URL', encodeURIComponent(target)));
                    }
                });
            } else {
                iziToast.info({
                    title: 'Blank URL',
                    message: 'Please fill in a URL',
                    position: 'topRight'
                });
            }
        });
    });
</script>
@endsection
