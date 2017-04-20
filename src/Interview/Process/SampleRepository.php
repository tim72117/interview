<?php

namespace Interview\Process;

use Carbon;
use Input;
use Cdb;
use Set;

class SampleRepository
{
    public static function instance()
    {
        return new self();
    }

    public function get_babys($user)
    {
        $this->user = $user;
        $area = Cdb\Service::where('user_id', $this->user->id)->first();
        $babys = Cdb\Baby::with([
            'interviewer',
            'turn_records' => function ($query) {
                $query->where('finish', '=', 0);
            },
            'wave',
            'wave.books',
            'wave.books.wave',
            'nanny',
            'new_address',
            'visit_parent',
        ])
        ->where('interviewer_id', '=', $this->user->id)->get();
        return [
            'area'      => $area->area,
            'districts' => Cdb\District::select('name', 'country_name')->remember(10)->get(),
            'babys'     => $babys->map(function ($baby) use ($area) {
                $today =Carbon\Carbon::today();
                $today1 = Carbon\Carbon::today();
                $age_days = Carbon\Carbon::createFromFormat('Y-m-d', $baby->birthday)->diffInDays();
                $birthday = Carbon\Carbon::parse($baby->birthday)->subYears(1911);

                $active_wave = $baby->wave->filter(function ($wave) use ($age_days) {
                    return $age_days <= $wave->end && $age_days >= $wave->wait_start && $wave->active == 1;
                })->first();
                $simple_wave = $baby->wave->filter(function ($wave) use ($age_days) {
                    return $age_days <= $wave->end && $age_days >= $wave->wait_start && $wave->ques == 3;
                })->first();

                $wait_wave =  $simple_wave != null && $active_wave->month != $simple_wave->month
                    ? $baby->wave->filter(function ($wave) use ($simple_wave) {
                        return $wave->month == $simple_wave->month  && $wave->ques == 2;
                    })->first(): null;

                $book = $wait_wave == null ? $active_wave->books->filter(function ($book) {
                    return $book->start;
                })->first() : $wait_wave->books->filter(function ($book) {
                    return $book->start;
                })->first();

                $visit =$baby->visit_parent->filter(function ($visit) use ($baby, $active_wave) {
                    return ($visit->baby_id == $baby->id && $visit->wave_id == $active_wave->id && $visit->result != null) &&
                           ($visit->result == 1 || $visit->result == 0);
                })->first();

                $simple = isset($simple_wave)
                          ? $baby->visit_parent->filter(function ($visit) use ($baby, $simple_wave) {
                              return ($visit->baby_id == $baby->id && $visit->wave_id == $simple_wave->id && $visit->result != null) &&
                                       ($visit->result == 1 || $visit->result == 0);
                          })->first()
                          : null;

                $age_days - $active_wave->start >=0 ? $day = $active_wave->end - $age_days : $day = null;
                $age_days - $active_wave->start <0 ? ($wait_wave == null ? $close = $active_wave->start - $age_days : $close = $active_wave->start - $age_days) : $close = null;
                $simple_wave != null ? $day2 = $simple_wave->end - $age_days : $day2 = null;
                $day != null ? $today->addDays($day)->subYears(1911) : $today->addDays($close)->subYears(1911);
                $simple_wave != null && $today1->addDays($day2)->subYears(1911);
                $status = !empty($baby->turn_records[0]) ? Cdb\Struct_cdb::divert_status($baby->turn_records[0], $this->user, $area->role, $area->area) :  $status = array('id'=>'1', 'code'=>'進行中');
                return [
                    'identifier'        => $baby->identifier,
                    'id'                => $baby->id,
                    'status'            => $status,
                    'name'              => $baby->name,
                    'gender'            => $baby->gender,
                    'birthday'          => $birthday->year.'/'.$birthday->month.'/'.$birthday->day,
                    'country'           => $baby->country,
                    'address'           => $baby->new_address == null ? $baby->country.$baby->village.$baby->address : $baby->new_address->address,
                    'homeAddress'       => $baby->country.$baby->village.$baby->address,
                    'newAddress'        => $baby->new_address == null ? '同戶籍地' : $baby->new_address->address,
                    'interviewer'       => $baby->interviewer->toArray(),
                    'now_wave'          => $wait_wave == null  ? 0 : $active_wave,
                    'wave'              => $wait_wave != null  ? $wait_wave : $active_wave,
                    'book'              => $book,
                    'simple_wave'       => $simple_wave,
                    'simple_telBook'    => $simple_wave != null ? $simple_wave->books->filter(function ($book) {
                        return $book->type == 2 && $book->class == 5;
                    })->first() : null,
                    'parent_telBook'    => $wait_wave == null ? $active_wave->books->filter(function ($book) {
                        return $book->type == 2 && $book->class == 5;
                    })->first()
                                                              : $wait_wave->books->filter(function ($book) {
                                                                  return $book->type == 2 && $book->class == 5;
                                                              })->first(),
                    'age_days'          => $age_days,
                    'nanny'             => !$baby->nanny->filter(function ($nanny) {
                        return $nanny->ques == 4 && (($nanny->result == 0) || ($nanny->result != 0 && $nanny->warn2 == 1));
                    })->isEmpty(),
                    'school'            => !$baby->nanny->filter(function ($nanny) {
                        return $nanny->ques == 5 && (($nanny->result == 0) || ($nanny->result != 0 && $nanny->warn2 == 1));
                    })->isEmpty(),
                    'visit'             => isset($visit)  ? $visit->result : null,
                    'simple'            => isset($simple) ? $simple->result : null,
                    'parent_watch'      => isset($visit)  ? $active_wave->books->filter(function ($book) use ($active_wave) {
                        return $book->wave_id == $active_wave->id && $book->type == 2 && $book->class == 8;
                    })->first()
                                                          : null,
                    'simple_watch'      => isset($simple) ? $simple_wave->books->filter(function ($book) use ($simple_wave) {
                        return $book->wave_id == $simple_wave->id && $book->type == 2 && $book->class == 8;
                    })->first()
                                                          : null,
                    'parent_final'      => !$baby->visit_parent->filter(function ($visit) use ($baby, $active_wave) {
                        return ($visit->result == 5 && $visit->baby_id == $baby->id && $visit->wave_id == $active_wave->id);
                    })->isEmpty(),
                    'simple_final'      => isset($simple_wave) ? !$baby->visit_parent->filter(function ($visit) use ($baby, $simple_wave) {
                        return ($visit->result == 5 && $visit->baby_id == $baby->id && $visit->wave_id == $simple_wave->id);
                    })->isEmpty()
                                                               : false,
                    'age_days'          => $age_days,
                    'order'             => $status['id'],
                    'day'               => $day,
                    'close'             => $close,
                    'parentDate'        => $today->year.'/'.$today->month.'/'.$today->day,
                    'simpleDate'        => $simple_wave != null ? $today1->year.'/'.$today1->month.'/'.$today1->day : null,
                    'warn'              => $baby->warn,
                ];
            }),
        ];
    }

    public function updatebaby($user)
    {
        $this->user = $user;
        $user = Cdb\Service::where('user_id', $this->user->id)->first();
        $baby = Cdb\Baby::with([
            'interviewer',
            //'interviewer.managements.boss',
            'turn_records' => function ($query) {
                $query->where('finish', '=', 0);
            },
            'wave',
            'wave.books',
            'wave.books.wave',
            'visit_parent',
            'new_address'
        ])
        ->where('id', '=', Input::get('baby.id'))->first();
        if ($baby == null) {
            return ['visit' => 1];
        }
        $today =Carbon\Carbon::today();
        $today1 = Carbon\Carbon::today();
        $birthday = Carbon\Carbon::parse($baby->birthday)->subYears(1911);
        $black = null;

        if ($user->role == 1 || ($baby->reopen == null && ($user->role == 3 || $user->role == 4))) {
            $age_days = Carbon\Carbon::createFromFormat('Y-m-d', $baby->birthday)->diffInDays();
            $active_wave = $baby->wave->filter(function ($wave) use ($age_days) {
                return $age_days <= $wave->end && $age_days >= $wave->wait_start && $wave->active == 1;
            })->first();
            $simple_wave = $baby->wave->filter(function ($wave) use ($age_days) {
                return $age_days <= $wave->end && $age_days >= $wave->wait_start && $wave->ques == 3;
            })->first();
            $simple = isset($simple_wave)
                                ? $baby->visit_parent->filter(function ($visit) use ($baby, $simple_wave) {
                                    return ($visit->baby_id == $baby->id && $visit->wave_id == $simple_wave->id && $visit->result != null) &&
                                           ($visit->result == 1 || $visit->result == 0);
                                })->first()
                                : null;
        } elseif ($baby->reopen != null && ($user->role == 3 || $user->role == 4)) {
            $fool_days = $baby->reopen;
            $age_days = Carbon\Carbon::createFromFormat('Y-m-d', $baby->birthday)->diffInDays();
            $active_wave = $baby->wave->filter(function ($wave) use ($fool_days) {
                return $fool_days <= $wave->end && $fool_days >= $wave->wait_start && $wave->active == 1;
            })->first();
            $simple_wave = $baby->wave->filter(function ($wave) use ($age_days) {
                return $age_days <= $wave->end && $age_days >= $wave->wait_start && $wave->ques == 3;
            })->first();
            if ($simple_wave == null) {
                $simple_wave = $baby->wave->filter(function ($wave) use ($fool_days) {
                    return $fool_days <= $wave->end && $fool_days >= $wave->wait_start && $wave->ques == 3;
                })->first();
                $simple = isset($simple_wave)
                                  ? $baby->visit_parent->filter(function ($visit) use ($baby, $simple_wave) {
                                      return ($visit->baby_id == $baby->id && $visit->wave_id == $simple_wave->id && $visit->result != null) &&
                                               ($visit->result == 1 || $visit->result == 0);
                                  })->first()
                                  : null;
                $black = 3;
            } else {
                $black =2;
            }
        }

        $wait_wave =  $simple_wave != null && $active_wave->month != $simple_wave->month
                ? $baby->wave->filter(function ($wave) use ($simple_wave) {
                    return $wave->month == $simple_wave->month  && $wave->ques == 2;
                })->first(): null;

        $book = $wait_wave == null ? $active_wave->books->filter(function ($book) {
            return $book->start;
        })->first() : $wait_wave->books->filter(function ($book) {
            return $book->start;
        })->first();

        $visit =$baby->visit_parent->filter(function ($visit) use ($baby, $active_wave) {
            return ($visit->baby_id == $baby->id && $visit->wave_id == $active_wave->id && $visit->result != null) &&
                           ($visit->result == 1 || $visit->result == 0);
        })->first();

        $age_days - $active_wave->start >=0 ? $day = $active_wave->end - $age_days : $day = null;
        $age_days - $active_wave->start <0 ? ($wait_wave == null ? $close = $active_wave->start - $age_days : $close = $active_wave->start - $age_days) : $close = null;
        $simple_wave != null ? $day2 = $simple_wave->end - $age_days : $day2 = null;
        $day != null ? $today->addDays($day) : $today->addDays($close);
        $simple_wave != null && $today1->addDays($day2);
        $status = !empty($baby->turn_records[0]) ? Cdb\Struct_cdb::divert_status($baby->turn_records[0], $this->user, $area->area) :  $status = array('id'=>'1', 'code'=>'進行中');
        return [
                'identifier'        => $baby->identifier,
                'id'                => $baby->id,
                'status'            => $status,
                'name'              => $baby->name,
                'gender'            => $baby->gender,
                'birthday'          => $birthday->year.'/'.$birthday->month.'/'.$birthday->day,
                'country'           => $baby->country,
                'address'           => $baby->new_address == null ? $baby->country.$baby->village.$baby->address : $baby->new_address->address,
                'interviewer'       => $baby->interviewer->toArray(),
                'now_wave'          => $wait_wave == null  ? 0 : $active_wave,
                'wave'              => $wait_wave != null  ? $wait_wave : $active_wave,
                'book'              => $book,
                'simple_wave'       => $simple_wave,
                'simple_telBook'    => $simple_wave != null ? $simple_wave->books->filter(function ($book) {
                    return $book->type == 2 && $book->class == 5;
                })->first() : null,
                'parent_telBook'    => $wait_wave == null ? $active_wave->books->filter(function ($book) {
                    return $book->type == 2 && $book->class == 5;
                })->first()
                                                              : $wait_wave->books->filter(function ($book) {
                                                                  return $book->type == 2 && $book->class == 5;
                                                              })->first(),
                'age_days'          => $age_days,
                'nanny'             => !$baby->nanny->filter(function ($nanny) {
                    return $nanny->ques == 4 && (($nanny->result == 0) || ($nanny->result != 0 && $nanny->warn2 == 1));
                })->isEmpty(),
                'school'            => !$baby->nanny->filter(function ($nanny) {
                    return $nanny->ques == 5 && (($nanny->result == 0) || ($nanny->result != 0 && $nanny->warn2 == 1));
                })->isEmpty(),
                'visit'             => isset($visit)  ? $visit->result : null,
                'simple'            => isset($simple) ? $simple->result : null,
                'parent_watch'      => isset($visit)  ? $active_wave->books->filter(function ($book) use ($active_wave) {
                    return $book->wave_id == $active_wave->id && $book->type == 2 && $book->class == 8;
                })->first()
                                                          : null,
                'simple_watch'      => isset($simple) ? $simple_wave->books->filter(function ($book) use ($simple_wave) {
                    return $book->wave_id == $simple_wave->id && $book->type == 2 && $book->class == 8;
                })->first()
                                                      : null,
                'parent_final'      => !$baby->visit_parent->filter(function ($visit) use ($baby, $active_wave) {
                    return ($visit->result == 5 && $visit->baby_id == $baby->id && $visit->wave_id == $active_wave->id);
                })->isEmpty(),
                'simple_final'      => isset($simple_wave) ? !$baby->visit_parent->filter(function ($visit) use ($baby, $simple_wave) {
                    return ($visit->result == 5 && $visit->baby_id == $baby->id && $visit->wave_id == $simple_wave->id);
                })->isEmpty()
                                                            : false,
                'age_days'          => $age_days,
                'order'             => $status['id'],
                'day'               => $day,
                'close'             => $close,
                'parentDate'        => Carbon\Carbon::createFromFormat('Y-m-d', $today->toDateString())->toDateString(),
                'simpleDate'        => $simple_wave != null ? Carbon\Carbon::createFromFormat('Y-m-d', $today1->toDateString())->toDateString() : null,
                'warn'              => $baby->warn,
                'openPass'          => $baby->reopen != null ? $baby->reopen : 0,
                'black'             => $black
        ];
    }

    public function interviewer_divert()
    {
        $record = Input::only('baby_id', 'recipient', 'reason', 'sender');
        $record['sender_title'] = 1;
        $record['recipient_title'] = 3;
        $record['finish'] = 0;
        $record['notification'] = 0;
        Cdb\Turn_record::updateOrCreate($record);
        return ['saveStatus' => true];
    }

    public function verify()
    {
        $input = Input::only('baby_id');
        Cdb\Turn_record::updateOrCreate(array('baby_id'=>$input["baby_id"], 'finish'=>0, 'notification'=>0), array('finish'=>1, 'notification'=>1));
        return array('saveStatus'=>true);
    }

    public function updateNanny()
    {
        $nanny = Cdb\Nanny::with(['wave', 'wave.nanny_books'
                                     , 'wave.nanny_books.wave'])->where('id', '=', Input::get('nanny.id'))->where('result', '=', 0)
                                                                ->orWhere('id', '=', Input::get('nanny.id'))->where('result', '<>', 0)->where('warn2', '=', 1)->first();
        if ($nanny == null) {
            return ['time' =>null, 'final' => null];
        }
        $active_wave = $nanny->wave->filter(function ($wave) use ($nanny) {
            return $wave->month == $nanny->month;
        })->first();
        $watch = $active_wave != null ? Cdb\Visit_parent::where('wave_id', '=', $active_wave->id)->where('result', '=', 0)->where('nanny_id', '=', $nanny->id)->exists() : false;
        return [
            'time'       => Input::get('nanny.time'),
            'watch_book' => $watch ? $active_wave->nanny_books->filter(function ($book) {
                return $book->type == 2 && $book->class == 8;
            })->first() : null,
            'final'      => $active_wave != null ? Cdb\Visit_parent::where('wave_id', '=', $active_wave->id)->where('result', '=', 5)->where('nanny_id', '=', $nanny->id)->exists() : false,
            'warn'       => $nanny->warn,
            'warn2'      => $nanny->warn2,
        ];
    }

    public function visit_list()
    {
        $visits = Cdb\Visit_parent::with('wave')->where('baby_id', '=', Input::get('baby.id'))->orderBy('created_at', 'ASC')->get();

        $bases = Cdb\Baby_contact::where('baby_id', '=', Input::get('baby.id'))->orderBy('created_at', 'DESC')->get();

        $parents = Cdb\Baby_family::with('visit.wave')->where('baby_id', '=', Input::get('baby.id'))->where('ques', '=', 2)->orderBy('created_at', 'DESC')->get();
        $parent_agree = null;
        // $parent_agree = Cdb\agree::with('family')->where('ques', '=', Input::get('baby.wave.ques'))->where('month', '=', Input::get('baby.wave.month'))
        //                 ->get()->filter(function($agree) {return $agree->family->pay ==0 && $agree->family->ques ==3;})->first();

        $simples = Cdb\Baby_family::with('visit.wave')->where('baby_id', '=', Input::get('baby.id'))->where('ques', '=', 3)->orderBy('created_at', 'DESC')->get();
        $simple_agree = Cdb\Agree::with('family')->where('ques', '=', Input::get('baby.simple_wave.ques'))->where('month', '=', Input::get('baby.simple_wave.month'))->where('baby_id', Input::get('baby.id'))
                        ->get()->filter(function ($agree) {
                            return $agree->family != null && $agree->family->pay ==0 && $agree->family->ques ==2 ;
                        })->first();

        $nanny = Cdb\Nanny::with(['wave', 'wave.nanny_books'
                                     , 'wave.nanny_books.wave'])->where('ques', '=', 4)->where('baby_id', '=', Input::get('baby.id'))->where('result', '=', 0)
                                                                ->orWhere('ques', '=', 4)->where('baby_id', '=', Input::get('baby.id'))->where('result', '<>', 0)->where('warn2', '=', 1)->get()
                                     ->filter(function ($nanny) {
                                         $create_days = Carbon\Carbon::createFromFormat('Y-m-d', $nanny->created_at->format('Y-m-d'))->addDays(30)->diffInDays();
                                         $open_days = Carbon\Carbon::createFromFormat('Y-m-d', $nanny->created_at->format('Y-m-d'))->diffInDays();
                                         $get_day = $nanny->change != null ? Carbon\Carbon::createFromFormat('Y-m-d', $nanny->change)->addDays(59)->diffInDays() : null;

                                         if ($get_day != null && $get_day>59) {
                                             $nanny->result = 2;
                                             $nanny->save();
                                             $nanny->delete();
                                             return false;
                                         } elseif ($get_day == null && $nanny->pay == 0 && $create_days>30) {
                                             $nanny->result = 3;
                                             $nanny->save();
                                             $nanny->delete();
                                             return false;
                                         } elseif ($get_day == null && $nanny->pay == 1 && $open_days>59) {
                                             $nanny->result = 4;
                                             $nanny->save();
                                             $nanny->delete();
                                             return false;
                                         } else {
                                             return true;
                                         }
                                     });

        $school = Cdb\Nanny::with(['wave', 'wave.nanny_books'
                                     , 'wave.nanny_books.wave'])->where('ques', '=', 5)->where('baby_id', '=', Input::get('baby.id'))->where('result', '=', 0)
                                                                ->orWhere('ques', '=', 5)->where('baby_id', '=', Input::get('baby.id'))->where('result', '<>', 0)->where('warn2', '=', 1)->get()
                                     ->filter(function ($nanny) {
                                         $create_days = Carbon\Carbon::createFromFormat('Y-m-d', $nanny->created_at->format('Y-m-d'))->addDays(30)->diffInDays();
                                         $open_days = Carbon\Carbon::createFromFormat('Y-m-d', $nanny->created_at->format('Y-m-d'))->diffInDays();
                                         $get_day = $nanny->change != null ? Carbon\Carbon::createFromFormat('Y-m-d', $nanny->change)->addDays(59)->diffInDays() : null;

                                         if ($get_day != null && $get_day>59) {
                                             $nanny->result = 2;
                                             $nanny->save();
                                             $nanny->delete();
                                             return false;
                                         } elseif ($get_day == null && $nanny->pay == 0 && $create_days>30) {
                                             $nanny->result = 3;
                                             $nanny->save();
                                             $nanny->delete();
                                             return false;
                                         } elseif ($get_day == null && $nanny->pay == 1 && $open_days>59) {
                                             $nanny->result = 4;
                                             $nanny->save();
                                             $nanny->delete();
                                             return false;
                                         } else {
                                             return true;
                                         }
                                     });
        $count = 1;
        return[
            'records' => $visits->map(function ($visit) use (&$count) {
                return [
                    'index'     => $count++ ,
                    'id'        => $visit->id,
                    'ques'      => $visit->wave[0]['ques'],
                    'wave'      => $visit->wave[0]['month'],
                    'way'       => $visit->method,
                    'time'      => $visit->updated_at,
                    'type'      => $visit->reason,
                    'result'    => $visit->result,
                ];
            }),

            'base' => $bases->map(function ($base) {
                return [
                    'id'       => $base->id,
                    'name'     => $base->name,
                    //'month'    => $parent->month,
                    'tel'      => $base->tel,
                    // 'work_tel' => $parent->work_tel,
                    // 'phone'    => $parent->phone,
                    'email'    => $base->email,
                    'address'  => $base->address,
                    'remark'   => $base->remark,
                ];
            }),

            'parent' => $parents->map(function ($parent) {
                return [
                    'id'       => $parent->id,
                    'name'     => $parent->name,
                    'month'    => $parent->month,
                    'tel'      => $parent->tel,
                    'work_tel' => $parent->work_tel,
                    'phone'    => $parent->phone,
                    'email'    => $parent->email,
                    'address'  => $parent->address,
                    'remark'   => $parent->remark,
                ];
            }),

            'parent_agree' => $parent_agree,

            'simple' => $simples->map(function ($simple) {
                return [
                    'id'       => $simple->id,
                    'name'     => $simple->name,
                    'month'    => $simple->month,
                    'tel'      => $simple->tel,
                    'work_tel' => $simple->work_tel,
                    'phone'    => $simple->phone,
                    'email'    => $simple->email,
                    'address'  => $simple->address,
                    'remark'   => $simple->remark,
                ];
            }),

            'simple_agree' => $simple_agree,

            'nanny' => $nanny->map(function ($nanny) {
                $active_wave = $nanny->wave->filter(function ($wave) use ($nanny) {
                    return $wave->month == $nanny->month;
                })->first();
                $watch = $active_wave != null ? Cdb\Visit_parent::where('wave_id', '=', $active_wave->id)->where('result', '=', 0)->where('nanny_id', '=', $nanny->id)->exists() : false;
                $open_days = Carbon\Carbon::createFromFormat('Y-m-d', $nanny->created_at->format('Y-m-d'))->diffInDays()+1;
                $get_day   = $nanny->change != null ? Carbon\Carbon::createFromFormat('Y-m-d', $nanny->change)->diffInDays()+1 : null;
                if ($nanny->pay == 0) {
                    $closs_time = ($open_days+29)-$open_days;
                } elseif ($nanny->pay == 1 && $nanny->change != null) {
                    $closs_time = 61-$get_day;
                } elseif ($nanny->pay == 1 && $nanny->change == null) {
                    $closs_time = 61-$open_days;
                }
                return [
                    'id'         => $nanny->id,
                    'time'       => $closs_time,
                    'name'       => $nanny->name,
                    'month'      => $nanny->month,
                    'book'      => $active_wave != null ? $active_wave->nanny_books->filter(function ($book) {
                        return $book->start == true && $book->type == 4;
                    })->first() : null,
                    'tel_book'  => $active_wave != null ? $active_wave->nanny_books->filter(function ($book) {
                        return $book->type == 2 && $book->class == 5;
                    })->first() : null,
                    'watch_book' => $watch ? $active_wave->nanny_books->filter(function ($book) {
                        return $book->type == 2 && $book->class == 8;
                    })->first() : null,
                    'tel'        => $nanny->tel,
                    'work_tel'   => $nanny->work_tel,
                    'phone'      => $nanny->phone,
                    //'email'    => $nanny->email,
                    'address'    => $nanny->address,
                    'pay'        => $nanny->pay,
                    'status'     => $nanny->status,
                    'final'      => $active_wave != null ? Cdb\Visit_parent::where('wave_id', '=', $active_wave->id)->where('result', '=', 5)->where('nanny_id', '=', $nanny->id)->exists() : false,
                    'warn'       => $nanny->warn,
                    'warn2'      => $nanny->warn2,
                    'remark'     => $nanny->remark,
                    'change'     => $nanny->change
                ];
            }),
            'school' => $school->map(function ($school) {
                $active_wave = $school->wave->filter(function ($wave) use ($school) {
                    return $wave->month == $school->month;
                })->first();
                $watch = $active_wave != null ? Cdb\Visit_parent::where('wave_id', '=', $active_wave->id)->where('result', '=', 0)->where('nanny_id', '=', $school->id)->exists() : false;
                $open_days = Carbon\Carbon::createFromFormat('Y-m-d', $school->created_at->format('Y-m-d'))->diffInDays()+1;
                $get_day   = $school->change != null ? Carbon\Carbon::createFromFormat('Y-m-d', $school->change)->diffInDays()+1 : null;
                if ($school->pay == 0) {
                    $closs_time = ($open_days+29)-$open_days;
                } elseif ($school->pay == 1 && $school->change != null) {
                    $closs_time = 61-$get_day;
                } elseif ($school->pay == 1 && $school->change == null) {
                    $closs_time = 61-$open_days;
                }
                return [
                    'id'         => $school->id,
                    'time'       => $closs_time,
                    'name'       => $school->name,
                    'month'      => $school->month,
                    'book'       => $active_wave != null ? $active_wave->nanny_books->filter(function ($book) use ($school) {
                        return $book->start == true && $book->type == 4;
                    })->first() : null,
                    'tel_book'   => $active_wave != null ? $active_wave->nanny_books->filter(function ($book) use ($school) {
                        return $book->type == 2 && $book->class == 5;
                    })->first() : null,
                    'watch_book' => $watch ? $active_wave->nanny_books->filter(function ($book) {
                        return $book->type == 2 && $book->class == 8;
                    })->first() : null,
                    'tel'        => $school->tel,
                    'work_tel'   => $school->work_tel,
                    'phone'      => $school->phone,
                    //'email'    => $school->email,
                    'address'    => $school->address,
                    'class_name' => $school->class_name,
                    'school_name'=> $school->school_name,
                    'pay'        => $school->pay,
                    'status'     => $school->status,
                    'final'      => $active_wave != null ? Cdb\Visit_parent::where('wave_id', '=', $active_wave->id)->where('result', '=', 5)->where('nanny_id', '=', $school->id)->exists() : false,
                    'warn'       => $school->warn,
                    'warn2'      => $school->warn2,
                    'remark'     => $school->remark,
                    'change'     => $school->change
                ];
            }),
        ];
    }

    public function tel_visit()
    {
        if (Input::get('ques') == 'parent') {
            $book = Set\Book::where('wave_id', '=', Input::get('baby.wave.id'))->where('type', '=', 2)->where('class', '=', 5)->first();
            return ['book' => $book];
        } elseif (Input::get('ques') == 'simple') {
            $book = Set\Book::where('wave_id', '=', Input::get('baby.simple_wave.id'))->where('type', '=', 2)->where('class', '=', 5)->first();
            return ['book' => $book];
        }
    }
}