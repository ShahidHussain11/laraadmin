<?php
/**
 * Controller generated using LaraAdmin
 * Help: http://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: http://dwijitsolutions.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;
use Validator;
use DataTables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;
use Illuminate\Support\Str;

use App\User;

class UsersController extends Controller
{
	public $show_action = false;
	
	/**
	 * Display a listing of the Users.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Users');
		
		if(Module::hasAccess($module->id)) {
			return View('la.users.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => Module::getListingColumns('Users'),
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Display the specified user.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if(Module::hasAccess("Users", "view")) {
			$user = User::findOrFail($id);
			if(isset($user->id)) {
				if($user['type'] == "Employee") {
					return redirect(config('laraadmin.adminRoute') . '/employees/'.$user->id);
				} else if($user['type'] == "Client") {
					return redirect(config('laraadmin.adminRoute') . '/clients/'.$user->id);
				}
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("user"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Datatable Ajax fetch
	 *
	 * @return
	 */
	public function dtajax(Request $request)
	{
		$module = Module::get('Users');
		$listing_cols = Module::getListingColumns('Users');

		$values = DB::table('users')->select($listing_cols)->whereNull('deleted_at');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('Users');
		
		for($i=0; $i < count($data->data); $i++) {
			for ($j=0; $j < count($listing_cols); $j++) { 
				$col = $listing_cols[$j];
				if($fields_popup[$col] != null && Str::startsWith($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i]->$col = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i]->$col);
				}
				if($col == $module->view_col) {
					$data->data[$i]->$col = '<a href="'.url(config('laraadmin.adminRoute') . '/users/'.$data->data[$i]->id).'">'.$data->data[$i]->$col.'</a>';
				}
				// else if($col == "author") {
				//    $data->data[$i]->$col;
				// }
			}
		}
		$out->setData($data);
		return $out;
	}
}
