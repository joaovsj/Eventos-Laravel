<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    // defindo pra o MODEL que items é um tipode de dado diferente, como um array   
    protected $casts = [
        'items' => 'array'
    ];

    
    protected $dates = ['date'];

    // tudo que for enviado pelo POST pode ser atualizado sem nenhuma restrição
    protected $guarded = [];
    
    // método responsável por retornar o dono/Owner do evento
    public function user(){

        return $this->belongsTo('App\Models\User');
    }

    // método responsável por retornar os usuários do evento
    public function users(){
 
        return $this->belongsToMany('App\Models\User');
    }
}
