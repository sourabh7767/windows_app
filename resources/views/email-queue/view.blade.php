@extends('layouts.admin')
@section('title') Email @endsection
@section('content')

<!-- Main content -->
    <section>
       <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{route('user.home')}}">Home</a>
                                    </li>
                                     <li class="breadcrumb-item"><a href="{{route('page.index')}}">Page</a>
                                    </li>
                                    <li class="breadcrumb-item active">View
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
              </div>


        <div class="row">
          <div class="col-12">
            <div class="card">

              <!-- /.card-header -->
              <div class="card-body">

                     <table id="w0" class="table table-striped table-bordered detail-view">
                      <tbody>
                       <tr>
                          <th>ID</th>
                          <td colspan="1">{{$model->id}}</td>
                          <th>From Email</th>
                          <td colspan="1"><a href="mailto:jashely775@gmail.com">{{$model->from_email}}</a></td>
                        </tr>

                        <tr>
                          <th>To Email</th>
                          <td colspan="1"><a href="mailto:jashely775@gmail.com">{{$model->to_email}}</a></td>
                          <th>Subject</th>
                          <td colspan="1">{{$model->subject}}</td>
                        </tr>
                                   
                                    
                       <tr>
                        <th>Status</th>
                          <td colspan="1"><span class="badge badge-light-{{$model->getStatusBadge()}}">{{$model->getStatus()}}</span></td>
                          <th>Last Attempt</th>
                          <td colspan="1">{{$model->last_attempt}}</td>
                        </tr>

                         <tr>
                          <th>Attempts</th>
                          <td colspan="1">{{$model->attempts}}</td>
                           <th>Date Sent</th>
                          <td colspan="1">{{$model->date_sent}}</td>
                        </tr>

                         <tr>
                           <th>Created At</th>
                          <td colspan="1">{{$model->created_at}}</td>
                          <th>Updated At</th>
                          <td colspan="1">{{$model->updated_at}}</td>
                        </tr>
                      </tbody>
                    </table>

        
              </div>
          
              <!-- /.card-body -->

            </div>
            <!-- /.card -->
        </div>
       </div>  


        <div class="row">
          <div class="col-12">
            <div class="card">

              <!-- /.card-header -->
              <div class="card-body">
                {!!$model->message!!}
                <br>

                 <div class="row"> 
                    <div class="col-md-12 text-center">
                    <a id="tool-btn-manage"  class="btn btn-primary text-right" href="{{route('email-queue.index')}}" title="Back">Back</a>
                    </div>
                 </div>

              </div>
            </div>
          </div>
        </div> 

</section>


@endsection
