@extends('layout.app')

@section('title', "Upload")

@section('main-content')


    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Upload New Post</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('post.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" id="title" name="title"  >
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="thumbnail">Thumbnail</label>
                                <input type="file" class="form-control-file" id="thumbnail" name="thumbnail">
                            </div>

                             <div class="form-group">
                                 <label for="upload">File</label>
                                 <input type="file" class="form-control-file" id="upload" name="upload">
                             </div>

                            <div class="form-group">
                                <label for="location_view">Location (View)</label>
                                <input type="text" class="form-control" id="location_view" name="location_view">
                            </div>

                            <div class="form-group">
                                <label for="location_download">Location (Download)</label>
                                <input type="text" class="form-control" id="location_download" name="location_download">
                            </div>

                            <div class="form-group">
                                <label for="tags">Tags</label>
                                <input type="text" class="form-control" id="tags" name="tags" placeholder="tag1, tag2, tag3">
                            </div>

                            <button type="submit" class="btn btn-primary">upload</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection