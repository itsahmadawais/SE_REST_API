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
use App\Member;
use App\Notification;
use App\MessageSettings;
class Feed extends Controller
{
    public function feedSender()
    {
      $feed = Post :: orderBy('action_id','DESC')->get();
    foreach($feed as $key => $value)
    {
      $user = DB::table('engine4_users')
                  ->select('user_id',
                            'username',
                            'email',
                            'displayname',
                            'status',
                            'status_date',
                            'locale',
                            'language',
                            'timezone',
                            'search',
                            'show_profileviewers',
                            'level_id',
                            'enabled',
                            'verified',
                            'approved',
                            'creation_date',
                            'modified_date',
                            'lastlogin_date',
                            'update_date',
                            'member_count',
                            'view_count')
                  ->where('user_id',$feed[$key]['subject_id'])
                  ->first();
        $user_photo = DB::table('engine4_storage_files')
                  ->select('storage_path')
                  ->where('parent_type','user')
                  ->where('user_id',$user->user_id)
                  ->first();
        $attachments = [];
        
        if($feed[$key]->attachment_count>0)
        {
            $temp_attachment = DB::table('engine4_activity_attachments')
                                    ->join('engine4_album_photos','engine4_activity_attachments.id','engine4_album_photos.photo_id')
                                    ->join('engine4_storage_files','engine4_storage_files.file_id','engine4_album_photos.file_id')
                                    ->select('engine4_storage_files.storage_path')
                                    ->where('engine4_activity_attachments.action_id',$feed[$key]->action_id)
                                    ->get();
            foreach($temp_attachment as $temp_photo_id)
            {
                $attachments [] =  "https://ouvi-dizer.com/".$temp_photo_id->storage_path;
            }
        }
        
      if($user_photo==null)
      {
          $user_photo="";
      }
      else
      {
          $user_photo->storage_path="https://ouvi-dizer.com/".$user_photo->storage_path;
      }
      $user->user_photo=$user_photo->storage_path;
      $feed[$key]['user']=$user;
      $feed[$key]['attachments']=$attachments;

      $comments = DB::table('engine4_activity_comments')->where('resource_id',$feed[$key]['action_id'])->get();
      $feed[$key]['comments']=$comments;
    }
    $fd = [];
    foreach($feed as $fdd)
    {
        $fd['data'][]['feed'] = $fdd;
        $fd['status_code'] = 200;
    }
      return response()->json($fd, 200,['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }
    
    
    public function UserInfo($id=null)
    {
        if($id==null)
        {
            return response()->json(null,400);
        }
            $general = DB::table('engine4_users')
                  ->select('user_id',
                        'username',
                        'email',
                        'locale',
                        'language',
                        'timezone')                                        
                  ->where('user_id',$id)
                  ->first();
            $privacy = DB::table('engine4_users')
                  ->select('user_id',
                        'lastUpdateDate',
                        'lastLoginDate',
                        'inviteeName',
                        'profileType',
                        'memberLevel',
                        'profileViews',
                        'friendsCount',
                        'view_privacy',
                        'joinedDate')                                        
                  ->where('user_id',$id)
                  ->first();
            $msgSettings = DB::table('engine4_emessages_usersettings')                                     
                  ->where('user_id',$id)
                  ->first();
            $notification = DB::table('engine4_activity_notificationsettings')                                     
                  ->where('user_id',$id)
                  ->get();
            $emailNotification= DB::table('engine4_user_emailsettings')                                     
                  ->where('user_id',$id)
                  ->get();
            $blockedUsers = DB::table('engine4_user_block')                                     
            ->where('user_id',$id)
            ->get();
                  
            $data['general']=$general;
            $data['blocked_users']=$blockedUsers;
            $data['privacy']=$privacy;
            $data['messageSettings']=$msgSettings;
            $data['emailSettings']=$emailNotification;
            $data['notification']=$notification ;
            $user['user']=$data;
        return response()->json($user,200);
    }
    public function UpdateUser(Request $req){
       $id = $req->input('user.general.user_id');
       $memeber = null;
       if($id!=null)
       {
            $member = Member :: find($id);
       }
       else
       {
            return response()->json("User Not found!",404);
       }
       $member->locale=  $req->input('user.general.locale');
       $member->email=  $req->input('user.general.email');
       $member->language=  $req->input('user.general.language');
       $member->timezone=  $req->input('user.general.timezone');
       /*Privacy*/
       $member->lastUpdateDate=  $req->input('user.privacy.lastUpdateDate');
       $member->lastLoginDate=  $req->input('user.privacy.lastLoginDate');
       $member->inviteeName=  $req->input('user.privacy.inviteeName');
       $member->profileType=  $req->input('user.privacy.profileType');
       $member->memberLevel=  $req->input('user.privacy.memberLevel');
       $member->profileViews=  $req->input('user.privacy.profileViews');
       $member->friendsCount=  $req->input('user.privacy.friendsCount');
       $member->view_privacy=  $req->input('user.privacy.view_privacy');
       $member->joinedDate=  $req->input('user.privacy.joinedDate');
       $member->save();
       /*Activity Notification Settings*/
       $notifications = $req->input('user.notification');
       DB::table('engine4_activity_notificationsettings')                                     
                 ->where('user_id',$id)
                 ->delete();
       if(is_array($notifications) && count($notifications)>0)
       {
            foreach($notifications as $notify)
            {
                 $notification = DB::table('engine4_activity_notificationsettings')                                     
                 ->where('user_id',$id)->where('type',$notify['type'])
                 ->first();
                 if($notification!=null)
                 {
                       $notifySetting['email'] = $notify['email'];
                       DB::table('engine4_activity_notificationsettings')
                      ->where('user_id', $id)
                      ->where('type',$notify['type'])
                      ->update($notifySetting);
                 }
                 else
                 {
                       $notifySetting['user_id']=$id;
                       $notifySetting['type']=$notify['type'];
                       $notifySetting['email']=$notify['email'];
                       DB::table('engine4_activity_notificationsettings')
                        ->insert($notifySetting);
                 }
            }
       }
       /*Message Settings*/
       $msgSettings = $req->input('user.messageSettings');
       if($msgSettings!=null && count($msgSettings)>0)
       {
            $msgSetting=DB::table('engine4_emessages_usersettings')                                     
            ->where('user_id',$id)
            ->first();
            if($msgSetting!=null)
            {
                  $msgSetting=[];
                  foreach($msgSettings as $key=>$value)
                  {
                        $msgSetting[$key]=$value;
                  }
                  
                  DB::table('engine4_emessages_usersettings')
                  ->where('user_id', $id)
                  ->update($msgSetting);
            }
            else
            {
                  $msgSetting = new MessageSettings();
                  if(isset($msgSettings['messageSettings.otherlastseen']))
                  {
                        $msgSetting['otherlastseen']=$msgSettings['messageSettings.otherlastseen'];
                  }
                  else
                  {
                        $msgSetting['otherlastseen']=1;
                  }
                  if(isset($msgSettings['onlinestatus']))
                  {
                        $msgSetting['onlinestatus']=$msgSettings['onlinestatus'];
                  }
                  else
                  {
                        $msgSetting['onlinestatus']=1;
                  }
                  if(isset($msgSettings['receivemessage']))
                  {
                        $msgSetting['receivemessage']=$msgSettings['receivemessage'];
                  }
                  else
                  {
                        $msgSetting['receivemessage']=1;
                  }
                  if(isset($msgSettings['status']))
                  {
                        $msgSetting['status']=$msgSettings['status'];
                  }
                  else
                  {
                        $msgSetting['status']=1;
                  }
                  $msgSetting['user_id']=$id;
                  DB::table('engine4_emessages_usersettings')
                  ->update($msgSetting);
            }
       }
       /*Email Settings*/
       $emailSettings=$req->input('user.emailSettings');
       $emailSetting = DB::table('engine4_user_emailsettings')                                     
                  ->where('user_id',$id)
                  ->delete();
       if($emailSettings!=null && is_array($emailSettings) && count($emailSettings)>0)
       {
            foreach($emailSettings as $key=>$value)
            {
                  $emailSetting = DB::table('engine4_user_emailsettings')                                     
                  ->where('user_id',$id)
                  ->where('type',$emailSettings[$key]['type'])
                  ->first();
                  if($emailSetting!=null)
                  {
                        DB::table('engine4_user_emailsettings')                                     
                        ->where('user_id',$id)
                        ->where('type',$emailSettings[$key]['type'])
                        ->update(["email"=>$emailSettings[$key]['email']]);
                  }
                  else
                  {
                        DB::table('engine4_user_emailsettings')
                        ->insert(['user_id'=>$id,'type'=>$emailSettings[$key]['type'],'email'=>$emailSettings[$key]['email']]);
                  }
            }
       }
       $blockedUsers= $req->input('user.blocked_users');
       if($blockedUsers!=null)
       {
             if(count($blockedUsers)>0)
             {
                   $blockedList = DB::table('engine4_user_block')                                     
                        ->where('user_id',$id)
                        ->get();
                   foreach($blockedUsers as $key=>$value)
                   {
                        if(count($blockedList)>0)
                        {
                              $flag=false;
                              foreach($blockedList as $blockedListItem)
                              {

                                    
                                    $blocked = DB::table('engine4_user_block')                                     
                                    ->where('user_id',$id)
                                    ->where('blocked_user_id',$blockedUsers[$key]['blocked_user_id'])
                                    ->first();
                                    if($blocked==null)
                                    {
                                          DB::table('engine4_user_block')
                                          ->insert(['user_id'=>$id,'blocked_user_id'=>$blockedUsers[$key]['blocked_user_id']]);
                                    }
                              }
                        }
                        else
                        {
                              DB::table('engine4_user_block')
                              ->insert(['user_id'=>$id,'blocked_user_id'=>$blockedUsers[$key]['blocked_user_id']]);
                        }
                   }
                   /*UnBlock Users */
                   $unblockedList = $req->input('user.unblocked_user');
                   if($unblockedList!=null)
                   {
                         foreach($unblockedList as $unblockedListItem)
                         {
                              DB::table('engine4_user_block')->where('blocked_user_id',$unblockedListItem['blocked_user_id'])->where('user_id',$id)->delete();
                         }
                   }
             }
       }
       else
       {
            DB::table('engine4_user_block')->where('user_id',$id)->delete();
       }
       return response()->json("Settings Updated!",200);
    }
    public function userPersonalInfo($id)
    {
        $datas = DB::table('engine4_user_fields_meta')
        ->join('engine4_user_fields_values','engine4_user_fields_values.field_id','=','engine4_user_fields_meta.field_id')
        ->where('engine4_user_fields_values.item_id',$id)
        ->select('engine4_user_fields_meta.alias','engine4_user_fields_values.value')
        ->get();
        $user=[];
        $personal=[];
        $contact=[];
        foreach($datas as $data)
        {
            $value=$data->value;
            switch ($data->alias)
            {
                case 'first_name':
                    $user['first_name']=$value;
                    break;
                case 'last_name':
                    $user['last_name']=$value;
                    break;
                case 'gender':
                    $user['gender']=$value;
                    break;
                 case 'birthdate':
                    $user['birthdate']=$value;
                    break;
                case 'relationship_status':
                    $user['relationship_status']=$value;
                    break;
                case 'location':
                    $user['location']=$value;
                    break;
                case 'location':
                    $user['location']=$value;
                    break;
                case 'about_me':
                    $user['about_me']=$value;
                case 'profession':
                    $personal['profession']=$value;
                case 'facebook':
                    $contact['facebook']=$value;
                    break;
                case 'twitter':
                    $contact['twitter']=$value;
                    break;
                default:
                    break;
            }
            
        }
        $userData['user_id']=$id;
        $userData['Personal_Information']=$user;
        $userData['Contact_Information']=$contact;
        $userData['Personal_Details']=$personal;
        return response()->json($userData,200);
    }
    public function updateUserPersonalInfo(Request $request)
    {
        $id = $request->input('user_id');
         foreach($request->all() as $key => $value)
         {
             if($key!="user_id" and count($request->input($key))>0)
             {
                 foreach ($request->input($key) as $k=>$v)
                 {
                     $data = DB::table('engine4_user_fields_meta')
                      ->where('alias', $k)
                      ->first();
                     if($v!=null && $v!="")
                     {
                         $datas = DB::table('engine4_user_fields_values')
                      ->where('field_id',$data->field_id)
                      ->where('item_id',$id)
                      ->update(['value'=>$v]);
                     }
                    
                 }
             }
         }

        return response()->json("Data updated!",200);
    }
        
}
