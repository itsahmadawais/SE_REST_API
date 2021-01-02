<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Post;
use App\Activity;
use App\Mainstorage;
use App\Attachment;
use App\Photo;
use Hash;
use DB;



class PostController extends Controller
{
  
    public function index()
   {
   	   $posts  = Post::take(5)->get();

   	   return $posts;
   }

    public function create(Request $request)
    {
    	$post = new Post();

        $post->post = $request->input('post');
        $post->image = $request->input('image');
        $post->link = $request->input('link');

        $post->save();

        return response()->json($post);
    }
    public function createnew(Request $request){
        $p =new Post;
        $p->type="status";
        $p->subject_type="user";
        $p->object_type="user";
        $p->subject_id=4;
        $p->object_id=4;
        $p->body="&lt;b class=ouvi_dizer_que_bold&gt;Ouvi dizer que&lt;/b&gt; ".$request->body;
        $p->params="[]";
        $p->date=date('Y-m-d H:i:s');
        $p->modified_date=date('Y-m-d H:i:s');
        $p->attachment_count=0;
        $p->comment_count=0;
        $p->like_count=0;
        $p->privacy=$request->privacy;
        
        $p->save();
        $np=Post::latest()->first();
        $q=Post::latest()->first();
          if($file = $request->hasFile('image')) {
              $fpic=new Mainstorage;
        $file = $request->file('image') ;
        $fileName = $file->getClientOriginalName() ;
        $destinationPath = public_path().'/images/' ;
        $file->move($destinationPath,$fileName);
        $fpic->parent_type="user";
        $fpic->user_id=2;
        $fpic->modified_date=date('Y-m-d H:i:s');
        $fpic->creation_date=date('Y-m-d H:i:s');
        $fpic->service_id=1;
        $fpic->extension="PNG";
        $fpic->mime_major="image";
        $fpic->mime_minor="png";
        $fpic->size="1200";
        $picpath='restapi_anonymous/public/images/'.$fileName;
        $fpic->hash=Hash::make($picpath);
        $fpic->storage_path = 'restapi_anonymous/public/images/'.$fileName ;
        $fpic->save();
        $nfpic=Mainstorage::latest()->first();
        $ph=new Photo;
        $ph->album_id="200";
        $ph->title="";
        $ph->description="";
         $ph->modified_date=date('Y-m-d H:i:s');
        $ph->creation_date=date('Y-m-d H:i:s');
        $ph->owner_type="user";
        $ph->owner_id=$request->target_id;
        $ph->file_id=$nfpic->file_id;
         $ph->view_count=0;
         $ph->comment_count=0;
         $ph->like_count=0;
         $ph->save();
         $updte=Photo::latest()->first();
         $updte->order=$updte->photo_id;
         $updte->save();
         $at=new Attachment;
         $at->action_id=$np->action_id;
         $at->type="album_photo";
         $at->id=$updte->photo_id;
         $at->mode=1;
         $at->save();
         $upf=Mainstorage::latest()->first();
         $upf->parent_id=$updte->photo_id;
         $upf->save();
         $np->attachment_count=1;
         $np->save();
         
         
        
    }
    
        
        $activity=new Activity;
        $activity->target_type="network";
        $activity->target_id=8;
        $activity->subject_type="user";
        $activity->subject_id=4;
        $activity->object_type="user";
        $activity->object_id=4;
        $activity->type="status";
        $activity->action_id=$q->action_id;
        $activity->save();
        $nactivity=new Activity;
        $nactivity->target_type="owner";
        $nactivity->target_id=4;
        $nactivity->subject_type="user";
        $nactivity->subject_id=4;
        $nactivity->object_type="user";
        $nactivity->object_id=4;
        $nactivity->type="status";
        $nactivity->action_id=$q->action_id;
        $nactivity->save();
        $nnactivity=new Activity;
        $nnactivity->target_type="parent";
        $nnactivity->target_id=2;
        $nnactivity->subject_type="user";
        $nnactivity->subject_id=4;
        $nnactivity->object_type="user";
        $nnactivity->object_id=4;
        $nnactivity->type="status";
        $nnactivity->action_id=$q->action_id;
        $nnactivity->save();
        
       return response()->json(['success' => 'success', 200]);
        
        
        
    
    }
 
  
}
