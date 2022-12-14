<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\incidents;
use App\Models\updates;
use Auth;
use App\Models\department;
use App\Models\priority;
use App\Models\sites;
use App\Models\status_history;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Interfaces\optionalFields;
use App\Models\Settings;
use App\Service\TicketWorkflow;
use Session;
use App\Models\Attachments;



class TicketController extends Controller implements optionalFields
{

    public $settings;

    public function __construct()
    {

        $this->settings = Settings::where('type','fields')->first();

    }


    public function create()
    {
        $sites = null;
        $departments = null;


        if($this->isToBeShown('location')) { $sites = Sites::all(); }
        if($this->isToBeShown('department')) { $departments = department::all(); }
        $subCategory = $this->isToBeShown('subcategory');

        $deptMandatory = $this->isMandatory('department');
        $locMandatory = $this->isMandatory('location');
        $subMandatory = $this->isMandatory('subcategory');


        $priorities = priority::all();

        return view('ticket.create-ticket', compact(['sites', 'priorities', 'departments','subCategory', 'deptMandatory','locMandatory','subMandatory']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, TicketWorkflow $ticketWorkflow)
    {

        $array = [
            'title' => 'required|min:5|max:250',
            'category' => 'required',
            'priority' => 'required',
            'comment' => 'required',

        ];


        if($this->isMandatory('department'))
        {
            $array['department'] =  'required';
        }
        if($this->isMandatory('location'))
        {
            $array['site'] =  'required';
        }
        if($this->isMandatory('subcategory'))
        {
            $array['sub_category'] = 'required';
        }

        $validated = $request->validate($array);


        $array = [
            'status' => 1,
            'title' => $request->title,
            'priority' => $request->priority,
            'created_by' => Auth::id(),
            'category' => $request->category,
            'site' => $request->site,
            'department' => $request->department,
            'sub_category' => $request->sub_category,
        ];

        $return = incidents::create($array);

        $files = $request->file('attachments');

        if($files)
        {
            foreach($files as $file)
            {

                $fileName = time() . '-' . $file->getClientOriginalName();
                $path = $file->storeAs('public\uploads', $fileName);
                Attachments::create(['incident' => $return->id, 'file_name' => $fileName]);
            }
        }


        $comment = [
            'comment' => $request->comment,
            'incident_no' => $return->id,
            'user_id' => Auth::id()
        ];


        updates::create($comment);

        $history = ['incident_id' => $return->id,
                    'status' => 1,
                    'user_id' => Auth::id(),
        ];

        status_history::create($history);

        $return = $ticketWorkflow->newTicket(incidents::find($return->id));

        Session::put('status', 'Success');


        return redirect()->back();

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(incidents $ticket)
    {

        $attachments = $ticket->has('attachments')->get();
        //dd(count($ticket->attachments));

        throw_if(
            !Auth::user()->isAdmin() && $ticket->created_by != Auth::id(),
            AuthorizationException::class,
            'You are not allowed to access this!'
        );


        $ticket->load(['requested_by','assigned_agent']);


        /*
        $ticket->load(['ticket_updates' => function($query) {
            $query->orderBy('created_at');
        }]);
        */
        return view('ticket.edit-ticket', compact('ticket'));
    }



    public function isToBeShown($choice)
    {

        $optional = $this->settings->json;

       foreach($optional as $field)
       {
         if($field['field'] == $choice)
         {
            //dd($field);
            return $field['active'];

         }

       }


        //$key = array_search($field, array_column($optional,$field));

        //$key = array_search('location', array_column($optional,'field'));

        //dd($key);


    }

    public function isMandatory($field)
    {

        $settings = $this->settings->json;

        $key = 0;
        foreach($settings as $setting)
        {

            if($setting['field'] == $field)
            {
                break;
            }
            $key++;

        }

//dd($settings[$key]['active'] == 'true' && $settings[$key]['mandatory'] == 'true');
        if($settings[$key]['active'] == 'true' && $settings[$key]['mandatory'] == 'true')
        {

            return true;
        }
        else {

            return false;
        }

    }
}
