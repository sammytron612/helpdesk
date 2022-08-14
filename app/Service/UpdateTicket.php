<?php
namespace App\Service;

use App\Models\incidents;
use App\Models\status_history;
use Auth;

class UpdateTicket
{
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function assign_self($incident)
    {
        
        $incident->status = 4;
        $incident->assigned_to = Auth::id(); 
        $incident->assigned_group = NULL;
        $incident->save();
        

        $history = ['incident_id' => $incident->id,
                'status' => 4,
                'user_id' => Auth::id(),
                'assigned_to' => Auth::id()
        ];
        

        status_history::create($history);

        return $incident->assigned->name;
      
    }

    public function assign_to($incident, $user_id)
    {
       
        $incident->status = 4;
        $incident->assigned_to = $user_id; 
        $incident->assigned_group = NULL;
        $incident->save();

        $history = ['incident_id' => $incident->id,
                'status' => 4,
                'user_id' => Auth::id(),
                'assigned_to' => $user_id
        ];

        status_history::create($history);
        
        return $incident->assigned->name;

    }

    public function assign_to_group($incident, $group_id)
    {

        $incident->status = 1;
        $incident->assigned_to = NULL;
        $incident->assigned_group = $group_id; 
        $incident->save();

        $history = ['incident_id' => $incident->id,
                'status' => 4,
                'user_id' => Auth::id(),
                'assigned_group' => $group_id
        ];

        status_history::create($history);
        //dd($incident);
        return $incident->group->name;
    }

    public function resolve($incident)
    {
        $incident->status = 5;
        $incident->assigned_to = Auth::id();
        $incident->assigned_group; 
        $incident->save();

        $history = ['incident_id' => $incident->id,
                'status' => 5,
                'user_id' => Auth::id(),
                'assigned_group' => NULL,
        ];

        status_history::create($history);

        return;
    }

    public function updateIncident($incident, $status)
    {
        $incident->status = $status;
        $incident->save();
        $history = status_history::where('incidet_id', $incident->id)->orderBy('created_at','desc')->first();

        $assigned_to = $history->assgined_to;
        $assigned_group = $history->assgined_group;

        $history = ['incident_id' => $incident->id,
                'status' => 5,
                'user_id' => Auth::id(),
                'assigned_to' => $assigned_to,
                'assigned_group' => $assigned_group
                
        ];

        status_history::create($history);

        return;
    }
    
}