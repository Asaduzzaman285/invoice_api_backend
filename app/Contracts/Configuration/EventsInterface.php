<?php

namespace App\Contracts\Configuration;
use Illuminate\Http\Request;

interface EventsInterface {
    public function filterData(Request $request);

    public function paginate(Request $request);

    public function show( $id );

    public function store( Request $request );

    public function update(Request $request );
}
