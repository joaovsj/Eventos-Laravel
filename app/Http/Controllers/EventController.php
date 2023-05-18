<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\Event;
use \App\Models\User;


class EventController extends Controller
{

    public function index(){

        $search = request('search');

        if($search){

            $events = Event::where([
                ['title', 'like', '%'.$search.'%']
            ])->get();

        } else {
    
            $events = Event::all();    
        }

        return view('welcome', compact('events', 'search'));
    }

    // responsável por retornar um form
    public function create(){
        return view('events.create');
    }

    // metodo responsável criar um recurso
    public function store(Request $request){
        
        $event = new Event();
        $event->title = $request->title;
        $event->date = $request->date;
        $event->city = $request->city;
        $event->private = $request->private;
        $event->description = $request->description;
        $event->items = $request->items;
        

        // arquivo FILE
        if($request->hasFile('image') and $request->file('image')->isValid()){

            $requestImage = $request->image;
            $extension = $requestImage->extension();
            // criando um nome
            $imageName = md5($requestImage->getClientOriginalName().strtotime("now")).".".$extension;
            // movendo pra pasta
            $requestImage->move(public_path('img/events'), $imageName);
            // definindo como indice da requisicao o novo nome            
            $event->image = $imageName;

        }

        // método que nos dá acesso ao usuário logado
        $user = auth()->user();
        $event->user_id = $user->id; // cadastrando chave estrangeira

        $event->save(); // persistindo no banco de dados
        return redirect('/')->with('msg', 'Dado criado com sucesso!');
    }

    // método responsável por retornar os dados baseado no ID
    public function show($id){

        // verificando se o usuário já participa desse evento
        $user = auth()->user();
        $hasUserJoined = false;

        if($user){
            // veririca os eventos desse usuario
            $userEvents = $user->eventsAsParticipant->toArray();

            foreach ($userEvents as $userEvent) {

                // ID que o o usuárion participa e ID do evento que veio como parâmetro
                if($userEvent['id'] == $id){
                    $hasUserJoined = true;
                }
            }
        }

        // procura registro e caso não encontre lança exceção
        $event = Event::findOrFail($id);

        $eventOwner = User::where('id', $event->user_id)->first()->toArray();

        return view('events.show', compact('event', 'eventOwner', 'hasUserJoined'));

    }

    // método responsável por esibir o dashboard
    public function dashboard(){

        $user = auth()->user();
        $events = $user->events; // retornando os eventos que o usuário é dono
        
        $eventsAsParticipant = $user->eventsAsParticipant; // retornando os eventos que ele participa

        return view('events.dashboard', compact('events', 'eventsAsParticipant'));
    }

    public function destroy($id){

        // deletando registro do banco
        Event::findOrFail($id)->delete();

        return redirect('/dashboard')->with('msg', 'Evento excluído com sucesso!');
    }


    // evento responsável por exibir o formulário para edição
    public function edit($id){

        $user = auth()->user();
        $event = Event::findOrFail($id);

        if($user->id != $event->user->id){
            return redirect('/dashboard');
        }

        return view('events.edit', compact('event'));
    }


    public function update(Request $request){


        $newEvent = $request->all();
        // arquivo FILE
        if($request->hasFile('image') and $request->file('image')->isValid()){

            $requestImage = $request->image;
            $extension = $requestImage->extension();
            // criando um nome
            $imageName = md5($requestImage->getClientOriginalName().strtotime("now")).".".$extension;
            // movendo pra pasta
            $requestImage->move(public_path('img/events'), $imageName);
            // definindo como indice da requisicao o novo nome            
            $newEvent['image'] = $imageName;

        }

        Event::findOrFail($request->id)->update($newEvent);
         
        return redirect('/dashboard')->with('msg', 'Evento Editado com Sucesso');
    }


    public function joinEvent($id){
        
        $user = auth()->user();
        $user->eventsAsParticipant()->attach($id); // attach => faz a ligação
        // vai inserir o id evento no id do usuario para aquele metodo

        $event = Event::findOrFail($id);

        return redirect('/dashboard')->with('msg', 'Sua presença está confirmada no evento '.$event->title);

    }

    /**
     * Método responsável por sair do evento 
     * @param $id -> evento
     */
    public function leaveEvent($id){

        $user = auth()->user();
        $user->eventsAsParticipant()->detach($id); // detach => desfaz a ligação 
        $event = Event::findOrFail($id);

        return redirect('/dashboard')->with('msg', 'Você saiu com sucesso do evento '.$event->title);


    }
}
 