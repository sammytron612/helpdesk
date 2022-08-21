<?php

namespace App\Http\Livewire\Incidents;

use Livewire\Component;
use App\Models\incidents;
use Livewire\WithPagination;
use Auth;
use Carbon\Carbon;

class IncidentTable extends Component
{

    use WithPagination;


    private $incidents;

    public $numberShown = 25;

    

    protected $listeners = ['changeSearch'];


    public function mount()
    {
        $this->incidents = incidents::paginate(25);

    }

    public function render()
    {
        return view('livewire.incidents.incident-table', ['incidents' => $this->incidents]);
    }


    public function changeSearch($number)
    {

        if ($number == 1) {
            $this->incidents = incidents::paginate($this->numberShown);
        } elseif ($number == 2) {
            $this->incidents = incidents::where('status', 4)->paginate($this->numberShown);
        } elseif ($number == 3) {
            $this->incidents = incidents::where('status', 1)->paginate($this->numberShown);
        } elseif ($number == 4) {
            $this->incidents = incidents::where('assigned_to', Auth::id())->paginate($this->numberShown);
        } elseif ($number == 5) {
            $this->incidents = incidents::paginate($this->numberShown);
        }
    }

   
}