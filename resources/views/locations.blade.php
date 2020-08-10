@extends('layouts.app')

@push('head')
    <!-- Scripts -->
    <script
        src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous"></script>

    <script defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GAPI_KEY', null) }}&callback=initMap"></script>

    <!-- Styles -->
    <link href="{{ asset('css/locations.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h3>Список работников</h3>
                    </div>
                    <div class="card-body locations-list">
                        @foreach ($workers as $worker)
                            <div class="row" id="{{ $worker->id }}">
                                <div class="divShow">
                                    <div>
                                        @if ($worker->first_name || $worker->last_name)
                                            <label class="name">{{ $worker->first_name.' '.$worker->last_name }}</label>
                                            <label class="phone">{{ $worker->phone }}</label>
                                        @else
                                            <label class="name">{{ $worker->phone }}</label>
                                        @endif
                                        <label class="text-muted lat-lon">[{{$worker->latitude.'; '.$worker->longitude}}]</label>
                                    </div>
                                    <div class="status on">
                                        <i class="fas fa-check fa-lg"></i>
                                    </div>
                                    <div class="status off">
                                        <i class="fas fa-times fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                            @unless ($loop->last)
                                <hr>
                            @endunless
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-body map-card">
                        <div id="map"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">

        const UPDATE_DELAY = 5 * 1000;
        const ACTIVE_DELAY = 60 * 1000;

        $(document).ready(function () {
            setInterval(updateWorkers, UPDATE_DELAY);
        });

        function addWorkerRow(worker) {
            const aboutInfo = (worker.first_name || worker.last_name) ? `
                <label class="name">` + worker.first_name + ' ' + worker.last_name + `</label>
                <label class="phone">` + worker.phone + `</label>
            ` : `
                <label class="name">` + worker.phone + `</label>
            `;
            $('.locations-list').append(`
                <div class="row" id="` + worker.id + `">
                    <div class="divShow">
                        <div>` +
                            aboutInfo + `
                            <label class="text-muted lat-lon">[` + worker.latitude + '; ' + worker.longitude + `]</label>
                        </div>
                        <div class="status on"><i class="fas fa-check fa-lg"></i></div>
                        <div class="status off"> <i class="fas fa-times fa-lg"></i></div>
                    </div>
                </div>
            `);
            $('.row#' + worker.id + ' .divShow').on('click', function () {
                locateToPlacemark(worker.id);
            });
        }

        function updateWorkers() {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '/workers',
                type: 'GET',
                success: function (response) {
                    response.forEach(worker => {
                        let marker = getPlacemark(worker.id);
                        if (marker == null) {
                            marker = addWorkerPlacemark(
                                worker.id,
                                worker.first_name,
                                worker.last_name,
                                worker.phone,
                                worker.latitude,
                                worker.longitude,
                                worker.last_update
                            );
                            addWorkerRow(worker);
                        }
                        const active = isActive(worker.last_update);
                        const row = $('.row#' + worker.id);
                        if (active) {
                            row.find('.status.on').show();
                            row.find('.status.off').hide();
                        } else {
                            row.find('.status.on').hide();
                            row.find('.status.off').show();
                        }
                        const icon = new google.maps.MarkerImage(active ? markerIconOn : markerIconOff,
                            null, null, null, new google.maps.Size(48, 48));
                        marker.setIcon(icon);
                        const latLng = new google.maps.LatLng(worker.latitude, worker.longitude);
                        marker.setPosition(latLng);
                    });
                },
            });
        }

        let map;
        const markers = [];

        function initMap() {
            const kiev = { lat: 50.26, lng: 30.31 };

            map = new google.maps.Map(document.getElementById('map'), { zoom: 10, center: kiev });
            @foreach ($workers as $worker)
                addWorkerPlacemark(
                    '{{ $worker->id }}',
                    '{{ $worker->first_name }}',
                    '{{ $worker->last_name }}',
                    '{{ $worker->phone }}',
                    '{{ $worker->latitude }}',
                    '{{ $worker->longitude }}',
                    '{{ $worker->last_update }}'
                );
            @endforeach

            const infoWindow = new google.maps.InfoWindow({map: map});
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    infoWindow.setPosition(pos);
                    infoWindow.setContent('Вы тут.');
                    map.setCenter(pos);
                }, function() {
                    handleLocationError(true, infoWindow, map.getCenter());
                });
            }
            updateWorkers();
        }

        function handleLocationError(browserHasGeolocation, infoWindow, pos) {
            infoWindow.setPosition(pos);
            infoWindow.setContent(browserHasGeolocation ?
                'Error: The Geolocation service failed.' :
                'Error: Your browser doesn\'t support geolocation.');
        }

        function isActive(last_update) {
            const now = Date.now() + new Date().getTimezoneOffset() * 60 * 1000;
            const last = new Date(last_update).getTime();
            console.log(now, last, last_update);

            return now - last < ACTIVE_DELAY;
        }

        function getWorkerBalloon(first_name, last_name, phone) {
            const content = { body: '', caption: '' };
            let name = first_name;
            if (last_name !== "") name += ' ' + last_name;
            if (name === '') {
                content.caption = phone;
                content.body = '<h4>' + phone + '</h4>';
            } else {
                content.caption = name;
                content.body = '<h4>' + name + '</h4><p>' + phone + '</p>';
            }
            return content;
        }

        const markerIconOn = '{{ asset("images/map-marker-on.svg") }}';
        const markerIconOff = '{{ asset("images/map-marker-off.svg") }}';

        function addWorkerPlacemark(id, first_name, last_name, phone, lat, lon, last_update) {
            const balloonContent = getWorkerBalloon(first_name, last_name, phone);
            const icon = isActive(last_update) ? markerIconOn : markerIconOff;

            const infoWindow = new google.maps.InfoWindow({
                content: balloonContent.body,
                maxWidth: 300
            });

            const marker = new google.maps.Marker({
                position: {lat: parseFloat(lat), lng: parseFloat(lon)},
                map: map,
                title: balloonContent.caption,
                icon: new google.maps.MarkerImage(icon, null, null, null, new google.maps.Size(48, 48)),
                id: id
            });

            marker.addListener('click', function () {
                infoWindow.open(map, marker);
            });
            markers.push(marker);
            return marker;
        }

        function getPlacemark(id) {
            for (let i = 0; i < markers.length; i++) {
                if (markers[i]['id'] == id) return markers[i];
            }
            return null;
        }

        function locateToPlacemark(id) {
            map.panTo(getPlacemark(id).getPosition());
        }

        $('.divShow').on('click', function () {
            locateToPlacemark($(this).parent().attr('id'));
        });

    </script>
@endsection
