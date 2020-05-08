@extends('layouts.app')

@push('head')
    <!-- Scripts -->
    <script
        src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous"></script>

    <script src="https://api-maps.yandex.ru/2.1/?apikey={{ env('YAPI_KEY', null) }}&lang=ru_RU" type="text/javascript"></script>

    <!-- Styles -->
    <link href="{{ asset('css/locations.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <div class="alert alert-danger"></div>
                        <form id="locationForm">
                            <input type="text" name="name" class="form-control" placeholder="Название">
                            <div class="input-group">
                                <input type="text" name="latitude" class="form-control" placeholder="Широта">
                                <input type="text" name="longitude" class="form-control" placeholder="Долгота">
                            </div>
                            <input type="submit" class="btn btn-dark" value="Добавить">
                        </form>
                    </div>
                    <div class="card-body locations-list">
                        @foreach ($locations as $location)
                            <div class="row" id="{{ $location->id }}">
                                <div class="divEdit">
                                    <form class="editForm">
                                        <input type="hidden" name="id" value="{{ $location->id }}">

                                        <label for="inputName" class="text-muted">Название</label>
                                        <input id="inputName" type="text" name="name" class="form-control" placeholder="Название" value="{{ $location->name }}">

                                        <label for="inputLatitude" class="text-muted">Широта</label>
                                        <input id="inputLatitude" type="text" name="latitude" class="form-control" placeholder="Широта" value="{{ $location->latitude }}">

                                        <label for="inputLongitude" class="text-muted">Долгота</label>
                                        <input id="inputLongitude" type="text" name="longitude" class="form-control" placeholder="Долгота" value="{{ $location->longitude }}">

                                        <button type="submit" class="btn btn-dark btn-sm apply"><i class="fas fa-check"></i></button>
                                    </form>
                                </div>
                                <div class="divShow">
                                    <div>
                                        <label class="name">{{ $location->name }}</label>
                                        <label class="text-muted lat">Широта: {{ $location->latitude }}</label>
                                        <label class="text-muted lon">Долгота: {{ $location->longitude }}</label>
                                    </div>
                                    <button class="btn btn-link edit" data-id="{{ $location->id }}"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-link delete" data-id="{{ $location->id }}"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </div>
                            @unless ($loop->last)
                                <hr>
                            @endunless
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body map-card">
                        <div id="map"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        let myMap;

        ymaps.ready(init);

        function init() {
            myMap = new ymaps.Map("map", {
                center: [55, 34],
                zoom: 7,
                controls: []
            }, {
                suppressMapOpenBlock: true,
                searchControlProvider: 'yandex#search'
            });

            const geolocation = ymaps.geolocation;

            geolocation.get({
                provider: 'yandex',
                mapStateAutoApply: true
            }).then(function (result) {
                result.geoObjects.options.set('islands#geolocationIcon');
                result.geoObjects.get(0).properties.set({
                    balloonContentBody: 'Ваше местоположение',
                    id: 0
                });
                myMap.geoObjects.add(result.geoObjects);
            });

            @foreach ($locations as $location)
            addPlacemark(
                '{{ $location->id }}',
                '{{ $location->name }}',
                '{{ $location->latitude }}',
                '{{ $location->longitude }}'
            );
            @endforeach
        }

        function addPlacemark(id, name, latitude, longitude) {
            const placemark = new ymaps.Placemark(
                [latitude, longitude], {
                    balloonContentBody: name,
                    id: id
                }, {
                    preset: 'islands#blackDotIcon',
                });
            myMap.geoObjects.add(placemark);
            locateToPlacemark(id);
        }

        function getPlacemark(id) {
            return ymaps.geoQuery(myMap.geoObjects).search('properties.id = "' + id + '"').get(0);
        }

        function locateToPlacemark(id) {
            myMap.setCenter(getPlacemark(id).geometry.getCoordinates(), 7);
        }

        function updatePlacemark(id, name, latitude, longitude) {
            const placemark = getPlacemark(id);
            placemark.geometry.setCoordinates([latitude, longitude]);
            placemark.properties.set('balloonContentBody', name);
            locateToPlacemark(id);
        }

        function removePlacemark(id) {
            myMap.geoObjects.remove(getPlacemark(id));
        }

        let $currentEditId = null;

        $('.edit').on('click', locationEdit);

        $('.delete').on('click', locationDelete);

        $('.divShow').on('click', function () {
            locateToPlacemark($(this).parent().attr('id'));
        });

        $('.editForm').on('submit', function (event) {
            event.preventDefault();
            locationUpdate(getFormData($(this))['id']);
        });

        function locationEdit(event) {
            event.preventDefault();
            if ($currentEditId !== null) {
                locationUpdate($currentEditId);
            }
            $currentEditId = $(this).attr('data-id');
            $('div.row#' + $currentEditId + '>.divEdit').show();
            $('div.row#' + $currentEditId + '>.divShow').hide();
        }

        function locationDelete(event) {
            event.preventDefault();
            if ($currentEditId !== null) {
                locationUpdate($currentEditId);
            }
            const $id = $(this).attr('data-id');
            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '/locations/delete/' + $id,
                type: 'POST',
                data: { 'id': $id },
                dataType: 'json',
                success: function (response) {
                    $('div.row#' + response.request.id).remove();
                    $('hr + hr').remove();
                    $('hr:first-child').remove();
                    $('hr:last-child').remove();
                    removePlacemark(response.request.id);
                },
            });
        }

        function locationUpdate(id) {
            if (id === null || id === undefined) return;
            const form = getFormData($('.row#' + id + ' .editForm'));
            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '/locations/' + id,
                type: 'POST',
                data: form,
                dataType: 'json',
                success: function (response) {
                    const currentRow = $('div.row#' + response.request.id);
                    currentRow.find('.name').text(response.request.name);
                    currentRow.find('.lat').text('Широта: ' + response.request.latitude);
                    currentRow.find('.lon').text('Долгота: ' + response.request.longitude);
                    currentRow.find('.divEdit').hide();
                    currentRow.find('.divShow').show();
                    if ($currentEditId === response.request.id ) {
                        $currentEditId = null;
                    }
                    updatePlacemark(
                        response.request.id,
                        response.request.name,
                        response.request.latitude,
                        response.request.longitude
                    );
                },
            });
        }

        function getFormData(form) {
            const unindexed_array = form.serializeArray();
            const indexed_array = {};

            $.map(unindexed_array, function(n, i){
                indexed_array[n['name']] = n['value'];
            });

            return indexed_array;
        }

        $('#locationForm').on('submit', function (event) {
            event.preventDefault();
            if ($currentEditId !== null) {
                locationUpdate($currentEditId);
            }
            $('.alert').empty().hide();
            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: '/locations',
                type: 'POST',
                data: getFormData($('#locationForm')),
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#locationForm')[0].reset();
                        if ($('.locations-list').children().length) {
                            $('.locations-list').prepend('<hr>');
                        }
                        $('.locations-list').prepend(`
                        <div class="row" id="` + response.request.id + `">
                                <div class="divEdit">
                                    <form class="editForm">
                                        <input type="hidden" name="id" value="` + response.request.id + `">

                                        <label for="inputName" class="text-muted">Название</label>
                                        <input id="inputName" type="text" name="name" class="form-control" placeholder="Name" value="` + response.request.name + `">

                                        <label for="inputLatitude" class="text-muted">Широта</label>
                                        <input id="inputLatitude" type="text" name="latitude" class="form-control" placeholder="Latitude" value="` + response.request.latitude + `">

                                        <label for="inputLongitude" class="text-muted">Долгота</label>
                                        <input id="inputLongitude" type="text" name="longitude" class="form-control" placeholder="Longitude" value="` + response.request.longitude + `">

                                        <button type="submit" class="btn btn-dark btn-sm apply"><i class="fas fa-check"></i></button>
                                    </form>
                                </div>
                                <div class="divShow">
                                    <div>
                                        <label class="name">` + response.request.name + `</label>
                                        <label class="text-muted lat">Широта: ` + response.request.latitude + `</label>
                                        <label class="text-muted lon">Долгота: ` + response.request.longitude + `</label>
                                    </div>
                                    <button class="btn btn-link edit" data-id="` + response.request.id + `"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-link delete" data-id="` + response.request.id + `"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </div>
                        `);
                        const newLocation = $('.row#' + response.request.id );
                        newLocation.find('.edit').click(locationEdit);
                        newLocation.find('.delete').click(locationDelete);
                        newLocation.find('.editForm').on('submit', function (event) {
                            event.preventDefault();
                            locationUpdate(getFormData($(this))['id']);
                        });
                        newLocation.find('.divShow').on('click', function () {
                            locateToPlacemark($(this).parent().attr('id'));
                        });
                        addPlacemark(
                            response.request.id,
                            response.request.name,
                            response.request.latitude,
                            response.request.longitude
                        );
                    } else {
                        $.each(response.errors, function(key, value){
                            $('.alert').show().append('<p>'+value+'</p>');
                        });
                    }
                },
            });
        });
    </script>
@endsection
