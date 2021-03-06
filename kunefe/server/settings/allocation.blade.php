{{-- Pterodactyl - Panel --}}
{{-- Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com> --}}

{{-- This software is licensed under the terms of the MIT license. --}}
{{-- https://opensource.org/licenses/MIT --}}
@extends('layouts.master')

@section('title')
    @lang('server.config.allocation.header')
@endsection

@section('content-header')
<div class="col-sm-12 col-md-6">
    <div class="header-bilgi">
        <i class="fas fa-server"></i>
        <ul class="list list-unstyled">
            <li><h1>@lang('server.config.allocation.header')</h1></li>
            <li><small>@lang('server.config.allocation.header_sub')</small></li>
        </ul>
    </div>
</div>
<div class="col-md-6 d-none d-lg-block">
    <div class="header-liste">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('index') }}">@lang('strings.home')</a></li>
            <li class="breadcrumb-item"><a href="{{ route('server.index', $server->uuidShort) }}">{{ $server->name }}</a></li>
            <li class="breadcrumb-item active">@lang('navigation.server.port_allocations')</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-8">
        <div class="card">
            <div class="card-header with-border">
                <h3 class="card-title">@lang('server.config.allocation.available')</h3>
            </div>
            <div class="card-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>@lang('strings.ip')</th>
                            <th>@lang('strings.alias')</th>
                            <th>@lang('strings.port')</th>
                            <th></th>
                        </tr>
                        @foreach ($allocations as $allocation)
                            <tr>
                                <td>
                                    <code>{{ $allocation->ip }}</code>
                                </td>
                                <td class="middle">
                                    @if(is_null($allocation->ip_alias))
                                        <span class="label label-default">@lang('strings.none')</span>
                                    @else
                                        <code>{{ $allocation->ip_alias }}</code>
                                    @endif
                                </td>
                                <td><code>{{ $allocation->port }}</code></td>
                                <td class="col-xs-2 middle">
                                    @if($allocation->id === $server->allocation_id)
                                        <a class="btn btn-sm btn-success disabled" data-action="set-default" data-allocation="{{ $allocation->hashid }}" role="button">@lang('strings.primary')</a>
                                    @else
                                        <a class="btn btn-sm btn-default" data-action="set-default" data-allocation="{{ $allocation->hashid }}" role="button">@lang('strings.make_primary')</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div id="toggleActivityOverlay" class="overlay hidden">
                <i class="fa fa-refresh fa-spin"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card">
            <div class="card-header with-border">
                <h3 class="card-title">@lang('server.config.allocation.help')</h3>
            </div>
            <div class="card-body">
                <p>@lang('server.config.allocation.help_text')</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('js/frontend/server.socket.js') !!}
    <script>
        $(document).ready(function () {
            @can('edit-allocation', $server)
            (function triggerClickHandler() {
                $('a[data-action="set-default"]:not(.disabled)').click(function (e) {
                    $('#toggleActivityOverlay').removeClass('hidden');
                    e.preventDefault();
                    var self = $(this);
                    $.ajax({
                        type: 'PATCH',
                        url: Router.route('server.settings.allocation', { server: Pterodactyl.server.uuidShort }),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content'),
                        },
                        data: {
                            'allocation': $(this).data('allocation')
                        }
                    }).done(function () {
                        self.parents().eq(2).find('a[role="button"]').removeClass('btn-success disabled').addClass('btn-default').html('{{ trans('strings.make_primary') }}');
                        self.removeClass('btn-default').addClass('btn-success disabled').html('{{ trans('strings.primary') }}');
                    }).fail(function(jqXHR) {
                        console.error(jqXHR);
                        var error = 'Bu iste??i i??leme almaya ??al??????rken bir hata olu??tu.';
                        if (typeof jqXHR.responseJSON !== 'undefined' && typeof jqXHR.responseJSON.error !== 'undefined') {
                            error = jqXHR.responseJSON.error;
                        }
                        Swal.fire({icon: 'error', title: 'Whoops!', html: error});
                    }).always(function () {
                        triggerClickHandler();
                        $('#toggleActivityOverlay').addClass('hidden');
                    })
                });
            })();
            @endcan
        });
    </script>
@endsection
