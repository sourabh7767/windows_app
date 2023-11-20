@extends('layouts.admin')
@section('title')Email Queue @endsection
@section('content')


  
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="content-header-left col-md-9 col-12 mb-2">
          <div class="row breadcrumbs-top">
              <div class="col-12">
                  <div class="breadcrumb-wrapper">
                      <ol class="breadcrumb">
                          <li class="breadcrumb-item"><a href="{{route('user.home')}}">Home</a>
                          </li>
                          <li class="breadcrumb-item"><a href="{{route('email-queue.index')}}">Email Queues</a>
                          </li>
                          <li class="breadcrumb-item active">Email Queue List
                          </li>
                      </ol>
                  </div>
              </div>
          </div>
      </div>


      <div class="row">
          <div class="col-12">
            <div class="card data-table">
               <div class="card-header">
                  <div class="heading-text">
                    <h4 class="m-0"><i class="fas fa-file mr-2"></i>&nbsp;{{ __('Email Queues') }}</h4>
                  </div>

                  <div class="right-side mr-2">

              </div>
              </div>
            
              <!-- /.card-header -->
              <div class="card-body">
                <table id="emailQueueTable" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>S.No</th>
                    <th>From Email</th>
                    <th>To Email</th>
                    <th>Subject</th>
                     <th>Status</th>
                    <th>Created At</th>
                    <th data-orderable="false">Action</th>
                  </tr>
                  </thead>
              
                </table>
              </div>
          
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
       </div>    
      <!-- /.container-fluid -->
    </section>  

  @push('page_script')

      @include('include.dataTableScripts')   

      <script src="{{ asset('js/pages/email-queue/index.js') }}"></script>

  @endpush

       
@endsection