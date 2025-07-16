<?php

namespace App\Contracts\Configuration;
use Illuminate\Http\Request;



interface CorporateServiceInterface
{
    public function paginate(Request $request);
    public function show($id);
    public function store(Request $request);
    public function update(Request $request);
    public function delete($id);
}