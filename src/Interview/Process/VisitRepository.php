<?php

namespace Plat\Interview;

use Input;
use Cdb;
use Set;
use DB;

class VisitRepository
{
    public static function instance()
    {
        return new self();
    }

    public function verifyVisit()
    {
        $check = 0;
        $method = Input::get('book.class') == 5 ? 1 : 0;
        if ((Input::get('book.type') ==2 && Input::get('book.class') ==5) || (Input::get('book.type') ==2 && Input::get('book.class') ==8)) {
            $record = Cdb\Visit_record::with('book.wave')->where('book_id', '=', Input::get('book.id'))->where('baby_id', '=', Input::get('baby.id'))->orderBy('created_at', 'DESC')->first();
            $visit = $record != null ? Cdb\Visit_parent::find($record->visit_id)->load('records.book.wave') : null;
            $simple = null;
        } else {
            $visit = Cdb\Visit_parent::with(['records' => function ($record) use (&$check) {
                if (Input::get('book.type') !=2 && Input::get('book.class') !=5) {
                    $check = 1;
                    $record->where('wave_id', Input::get('book.wave.id'))->orderBy('created_at', 'DESC')->get();
                }
            }, 'records.book.wave'])->where('baby_id', '=', Input::get('baby.id'))->where('nanny_id', '=', Input::get('nanny.id'))->where('wave_id', '=', Input::get('book.wave.id'))->where('method', '=', $method)
                                    ->orderBy('created_at', 'DESC')->first();

            $visit != null && $simple = Cdb\Visit_record::with('book.wave')->where('visit_id', $visit->id)->where('baby_id', Input::get('baby.id'))->orderBy('created_at', 'DESC')->first();
            $visit != null && $record = $visit->records->filter(function ($record) use ($visit) {
                return $record->visit_id == $visit->id;
            })->first();
        }


        if ($visit != null && $visit->result == null) {
            if ($simple != null && $check == 1 && ($simple->book[0]['class'] == 8 || $simple->book[0]['class'] == 5)) {
                Cdb\Baby::updateOrCreate(['id' => Input::get('baby.id')], ['warn' => 1]);
                return ['saveStatus'=>false];
            }

            if ($simple != null && $simple->id != $record->id) {
                return ['saveStatus'=>true, 'record' => $simple, 'visit' => $visit];
            } elseif ($record == null) {
                return ['saveStatus'=>false];
            } else {
                return ['saveStatus'=>true, 'record' => $record, 'visit' => $visit];
            }
        } else {
            if ((Input::get('book.wave.ques')==2 || Input::get('book.wave.ques')==3) && (Input::get('book.class')!=5 && Input::get('book.class')!=8)) {
                Cdb\Baby::updateOrCreate(['id' => Input::get('baby.id')], ['warn' => 1]);
            }
            if ((Input::get('book.wave.ques')==4 || Input::get('book.wave.ques')==5) && (Input::get('book.class')!=8)) {
                if (Input::get('book.class')==5) {
                    Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['warn2' => 1]);
                } else {
                    Cdb\Nanny::updateOrCreate(['id' => Input::get('nanny.id')], ['warn' => 1]);
                }
            }
            return ['saveStatus'=>false];
        }
    }

    public function quitVisit()
    {
        Input::get('record.extend') != 0 && Cdb\Visit_record::updateOrCreate(['id' => Input::get('record.id')], ['extend' => -1, 'rewriting' => 0]);
        if (Input::get('book.class') != 5) {
            $book = Set\Book::with('wave')->where('wave_id', '=', Input::get('record.wave_id'))->where('start', '=', 1)->where('class', '<>', 5)->first();
        } else {
            $book = Set\Book::with('wave')->where('wave_id', '=', Input::get('record.wave_id'))->where('type', '=', 2)->where('class', '=', 5)->first();
        }

        Cdb\Visit_parent::updateOrCreate(['id' => Input::get('record.visit_id')], ['reason' => '放棄本次紀錄', 'result' => 6]);
        return ['book'=>$book];
    }

    public function createVisit()
    {
        $visit = Cdb\Visit_parent::Create([
            'baby_id'        => Input::get('baby.id'),
            'wave_id'        => Input::get('book.wave_id'),
            'interviewer_id' => Input::get('baby.interviewer.id'),
            'nanny_id'       => Input::get('nanny.id'),
            'method'         => Input::get('book.class') == 5 ? 1 : 0,
        ]);

        return ['visit' => $visit];
    }

    public function createRecord($user)
    {
        $this->user = $user;
        if (Input::get('book.setBook_id') == null) {
            $extend = Set\Book::where('wave_id', Input::get('visit.wave_id'))->where('setBook_id', Input::get('book.id'))->first();
            if ($extend == null) {
                if ((Input::get('book.wave_id') ==  Input::get('visit.wave_id')) || (Input::get('book.wave_id') ==  Input::get('visit.wave_id')+11)) {
                    $book = Set\Book::find(Input::get('book.id'));
                } else {
                    return ['status' => false];
                }
            } else {
                $book = $extend;
            }
        } else {
            $book = Set\Book::find(Input::get('book.id'));
        }

        $record_old = $this->getRecord($book);

        if ($record_old != null) {
            if ($record_old->extend == -1) {
                if ($book->type == 1 && $book->class == 1) {
                    Cdb\Visit_record::find($record_old->id)->repositories->map(function ($repository) use ($record_old) {
                        if ($repository->string == null) {
                            return $repository->updateOrCreate(['record_id'  => $record_old->id, 'question_id'=> $repository->question_id, 'baby_id'    => $record_old->baby_id],
                                                           ['answer_id'  => null, 'string' => $repository->string, 'created_by' => $this->user->id]
                            );
                        }
                    });
                }
                return ['record' => Cdb\Visit_record::find($record_old->id), 'status' => true];
            } else {
                $record = Cdb\Visit_record::Create([
                    'visit_id' => Input::get('visit.id'),
                    'baby_id'  => Input::get('visit.baby_id'),
                    'wave_id'  => $book->wave_id,
                    'book_id'  => $book->id,
                    'rewriting' => 1,
                    'extend'   => -1,
                ]);
                $repositories = Cdb\Visit_record::find($record_old->id)->repositories->map(function ($repository) use ($record) {
                    return $repository->updateOrCreate(['record_id'  => $record->id, 'question_id'=> $repository->question_id, 'baby_id'    => $record->baby_id],
                                                       ['answer_id'  => $repository->answer_id, 'string' => $repository->string, 'created_by' => $this->user->id]
                    );
                });
                return ['record' => $record, 'status' => true];
            }
        } else {
            if ($book->type ==3) {
                $result = $this->continue_ques($book);
                if ($result['save']) {
                    return ['record' => $result['record'], 'status' => true];
                }
            }
            $record = Cdb\Visit_record::Create([
                'visit_id'  => Input::get('visit.id'),
                'baby_id'   => Input::get('visit.baby_id'),
                'wave_id'   => $book->wave_id,
                'book_id'   => $book->id,
                'extend'    => $book->rewrite ? -1 : 0,
            ]);
            return ['record' => $record, 'status' => true];
        }
    }

    private function continue_ques($book)
    {
        $success_book = Set\Book::where('wave_id', '=', $book->wave_id)->where('type', '=', 2)->where('class', '=', 3)->first();
        $stop_book    = Set\Book::where('wave_id', '=', $book->wave_id)->where('type', '=', 2)->where('class', '=', 6)->first();
        if ($success_book != null && $stop_book != null) {
            $success = Cdb\Visit_record::where('baby_id', '=', Input::get('visit.baby_id'))->where('wave_id', '=', $book->wave_id)->where('book_id', '=', $success_book->id)->exists();
            $stop    = Cdb\Visit_record::where('baby_id', '=', Input::get('visit.baby_id'))->where('wave_id', '=', $book->wave_id)->where('book_id', '=', $stop_book->id)->first();
            $ques = Cdb\Visit_record::where('baby_id', '=', Input::get('visit.baby_id'))->where('book_id', '=', $book->id)->where('wave_id', '=', $book->wave_id)->orderBy('created_at', 'DESC')->first();

            if (!$success && $stop != null) {
                $visit = Cdb\Visit_parent::where('baby_id', '=', Input::get('visit.baby_id'))->where('wave_id', '=', $book->wave_id)->orderBy('created_at', 'DESC')->get();
                $record = Cdb\Visit_record::Create([
                            'visit_id'  => Input::get('visit.id'),
                            'baby_id'   => Input::get('visit.baby_id'),
                            'wave_id'   => $book->wave_id,
                            'book_id'   => $book->id,
                            'extend'    => $book->rewrite ? -1 : 0,
                            ]);

                if ($book->exbook_id != null &&  $visit[1]['result'] == 6) {
                    $result = $this->createAnswer($book, $record);
                } else {
                    $repositories = Cdb\Visit_record::find($ques->id)->repositories->map(function ($repository) use ($record) {
                        $repository->Create([
                                'record_id'   => $record->id,
                                'question_id' => $repository->question_id,
                                'baby_id'     => $repository->baby_id,
                                'answer_id'   => $repository->answer_id,
                                'string'      => $repository->string,
                                'created_by'  => $this->user->id,
                            ]);
                    });
                }
                return ['save' => true, 'record' => $record];
            } elseif ($book->exbook_id != null && !isset($ques)) {
                $record = Cdb\Visit_record::Create([
                            'visit_id'  => Input::get('visit.id'),
                            'baby_id'   => Input::get('visit.baby_id'),
                            'wave_id'   => $book->wave_id,
                            'book_id'   => $book->id,
                            'extend'    => $book->rewrite ? -1 : 0,
                            ]);
                $result = $this->createAnswer($book, $record);
                return ['save' => true, 'record' => $record];
            }
        }
    }

    private function createAnswer($book, $record)
    {
        $start = microtime(true);

       //找出現在這本book需帶入值的問題
       $nq_query = Set\Question::whereNotNull('carry_question_id') -> where('book_id', $book->id);
        if ($book->id == 1127) {
            $nq_query = $nq_query->where('page', '<', 8);
        }
        $new_questions = $nq_query->get();

        $carryquestion_ids = array_map(function ($item) {
            return (int) $item;
        }, $new_questions -> lists('carry_question_id'));

       //找出上波可用資料的record_id
       $ex_record =  DB::table('cdb.dbo.visit_parents AS a ')
       ->Join('cdb.dbo.visit_records AS b', 'a.id', '=', 'b.visit_id')
       ->where('a.baby_id', $record->baby_id)
       ->where('b.book_id', $book->exbook_id)
       ->where('a.result', '=', 0)
       ->select('b.id')
       ->first();

        if (isset($ex_record)) {
            $ex_record = (int) $ex_record->id;
            //撈上波填答值
            $ex_ques_repos = DB::table('cdb.dbo.ques_repository AS a ')
            ->Join('plat_cdb.dbo.interview_set_answers AS b', 'b.id', '=', 'a.answer_id')
            ->Join('plat_cdb.dbo.interview_set_questions AS c', 'c.carry_question_id', '=', 'a.question_id')
            ->Join('plat_cdb.dbo.interview_set_answers AS d', function ($join) {
                $join->on('d.question_id', '=', 'c.id')
                    ->on('d.answer_id', '=', 'b.answer_id');
            })
            ->where('a.record_id', $ex_record)
            ->whereIn('a.question_id', $carryquestion_ids)
            ->orderBy('a.id', 'DESC')
            ->select('c.id AS question_id', 'd.id AS answer_id', 'a.string AS string')
            ->get();

            $visit = Cdb\Visit_parent::where('id', '=', $record->visit_id)->select('interviewer_id')->first();
            if (isset($ex_ques_repos)) {
                foreach ($ex_ques_repos as $ex_ques) {
                    Cdb\Ques_repository::Create([
                        'record_id'   => $record->id,
                        'question_id' => $ex_ques->question_id,
                        'baby_id'     => $record->baby_id,
                        'answer_id'   => $ex_ques->answer_id,
                        'string'      => $ex_ques->string,
                        'created_by'  => $visit->interviewer_id,
                        ]);
                }
            }
        }
        return ['save' => true];
    }

    private function getRecord($book)
    {
        if ($book->rewrite) {
            $record = Cdb\Visit_record::with('wave')
                ->where('baby_id', '=', Input::get('visit.baby_id'))
                ->where('wave_id', '=', $book->wave_id)
                ->where('book_id', '=', $book->id)
                ->orderBy('created_at', 'DESC')->first();
        } else {
            $record = null;
        }

        return $record;
    }

    public function check()
    {
        $books = Set\Book::with('rules')->get();

        if ($books->isEmpty()) {
            return ['book' => null];
        }
        Input::get('record.rewriting') == true &&  Cdb\Visit_record::updateOrCreate(['id' => Input::get('record.id')], ['rewriting' => false]);
        $repositories = Cdb\Ques_repository::with('answer')->where('record_id', Input::get('record.id'))->lists('answer_id', 'question_id');

        foreach ($books as $book) {
            foreach ($book->rules as $rule) {
                $status = true;
                $open = false;
                $expression = json_decode($rule->expression);
                $parameter = $expression->parameters[0];
                $question_id = key((array)$parameter);

                if ($rule->is->expression=='r1&&r2&&r3' || $rule->is->expression=='r1&&r2') {
                    foreach ($rule->is->parameters as $parameter) {
                        $key=key((array)$parameter);
                        if (array_key_exists($key, $repositories) && $repositories[$key] != $parameter->$key) {
                            $status = false;
                            break;
                        }
                        if (!array_key_exists($key, $repositories)) {
                            $status = false;
                            break;
                        }
                    }
                }
                if ($rule->is->expression == 'r1' && (!isset($repositories[$question_id]) || $repositories[$question_id] != $parameter->$question_id)) {
                    $status = false;
                }

                if ($rule->is->expression == 'b1') {
                    $status = false;
                }

                if ($status) {
                    $getBooks = $rule->jumpBook;
                    foreach ($getBooks as $getBook) {
                        if ($getBook->wave_id == Input::get('record.wave_id') || $getBook->wave_id == Input::get('record.wave_id')+11) {
                            $book = $getBook;
                        }
                    }
                    $getWaves = $rule->openWave;
                    if (isset($getWaves)) {
                        foreach ($getWaves as $getWave) {
                            if ($getWave->id == Input::get('record.wave_id')+11) {
                                $baby = Cdb\Baby::find(Input::get('baby.id'));
                                $baby->wave()->where('wave_id', '=', $getWave->id)->get()->isEmpty() && $baby->wave()->attach($getWave->id);
                                $open = true;
                            }
                        }
                    }
                    if (Input::get('book.type') == 1 && Input::get('book.class') == 1) {
                        Input::get('book.wave.ques') == 2 && $this->baby_confirm(Input::get('baby'));
                    }
                    return ['book' => Set\Book::find($book->id)->load('wave'), 'open' => $open, 'warning' => $rule->warning];
                }
            }
        }
        if (!$status) {
            $bookSelect = Set\Book::with('rules')->where('wave_id', Input::get('book.wave_id'))->get();
            if (Input::get('book.type') == 3) {
                Input::get('book.wave.ques') == 2 && InforRepository::instance()->ques_parent();
                foreach ($bookSelect as $book) {
                    if ($book->type == 2 && $book->class ==3) {
                        return ['book' => Set\Book::find($book->id)->load('wave')];
                    }
                }
            } else {
                return ['book' => null];
            }
        }
    }

    private function baby_confirm($baby)
    {
        $key = SaveAsRepository::instance()->key_save(Input::get('record.id'));

        if (array_key_exists("name", $key)) {
            Cdb\Baby::updateOrCreate(['id' => $baby['id']], ['name' => $key['name']['value']]);
        }
        if (array_key_exists("gender", $key)) {
            Cdb\Baby::updateOrCreate(['id' => $baby['id']], ['gender' => $key['gender']['value']]);
        }
    }

    public function rewrite()
    {
        if (Input::get('book.type') == 1 && Input::get('book.class') == 7) {
            Cdb\Wave_controller::where('baby_id', '=', Input::get('baby.id'))->where('wave_id', '=', Input::get('baby.simple_wave.id'))->delete();
        }
        Cdb\Visit_record::updateOrCreate(['id' => Input::get('record.id')], ['extend' => -2]);
    }

    public function confirmAnswers()
    {
        $record = Cdb\Visit_record::find(Input::get('record.id'));

        $answers = Cdb\Ques_repository::where('record_id', $record->id)->lists('answer_id', 'question_id');
        //$questions = $record->book->first()->questions()->where('page', Input::get('currentPage'))->with('is')->get();

        $questions = array_filter(Input::get('questions'), function ($question) use ($answers) {
            return !isset($answers[$question['id']]);
        });

        return ['confirmeds' => array_values($questions)];
    }
}
