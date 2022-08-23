<?php

namespace App\Http\Livewire\Notifications;

use Livewire\Component;
use App\Notifications\ChangeOwnership;
use App\Notifications\NewComment;
use Auth;
use Session;

class SocketNotification extends Component
{
    public $user;
    public $count = 0;
    public $notifications = [];

    

    public function mount()
    {
        $count = 0;
        $this->user = Auth::user();
    }

    public function getListeners()
    {
        return ["echo-private:newcomment.{$this->user->id},NewComment" => 'newComment',
                "echo-private:changeownershipagent.{$this->user->id},ChangeOwnershipAgent" => 'changeOwnershipAgent',
                
        ];
    }

    public function render()
    {
        
        if(session::has('notifications'))
        {
            
            $this->count = count(session('notifications'));
            $this->notifications = session('notifications');
        }
    

        return view('livewire.notifications.socket-notification', ['notifications' => $this->notifications]);
    }

    public function newIncident($data)
    {
        
      
    }

    public function newComment($data)
    {
        
        $array = ['id' => $this->count,
                'incidentId' => $data['incidentId'],
                'message' => "A new comment has been added to Incident No:{$data['incidentId']} titled `" . $data['title'] ."`"];

        session()->push('notifications',$array);
        
        
        $this->user->notify(new NewComment($data));

    }

    public function gotoIncident($incidentId,$id)
    {
        
        $this->removeNotification($id);

        return redirect()->to('/ticket/' . $incidentId . '/edit');
    }

    public function removeNotification($id)
    {
        
        foreach($this->notifications as $key => $value)
        {
            if($value['id'] == $id)
            {
                //dd('match');
                unset($this->notifications[$key]);
            }
        }
        session()->put('notifications', $this->notifications);

        return;
       
    }

    public function changeOwnershipAgent($data)
    {
        $array = ['id' => $this->count,
                'incidentId' => $data['incidentId'],
                'message' => "Your Incident No:{$data['incidentId']} titled `{$data['title']}` has been assigned to {$data['name']}"
            ];

        session()->push('notifications',$array);

        $this->user->notify(new ChangeOwnership($data));
        
    }

    public function changeOwnershipGroup()
    {

    }
    
}
