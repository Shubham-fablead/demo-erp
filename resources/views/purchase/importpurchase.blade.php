@extends('layout.app')

@section('title', 'Category List')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Import Purchase</h4>
            <h6>Add/Update Purchase</h6>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Supplier Name</label>
                        <div class="row">
                            <div class="col-lg-10 col-sm-10 col-10">
                                <select class="select">
                                    <option>Select</option>
                                    <option>Supplier</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-sm-2 col-2 ps-0">
                                <div class="add-icon">
                                    <a href="javascript:void(0);"><img src="admin/assets/img/icons/plus1.svg" alt="img"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Purchase Status </label>
                        <select class="select">
                            <option>Select</option>
                            <option>Status</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-12 col-sm-6 col-12">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <a class="btn btn-submit w-100">Download Sample File</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="form-group">
                        <label> Upload CSV File</label>
                        <div class="image-upload">
                            <input type="file">
                            <div class="image-uploads">
                                <img src="admin/assets/img/icons/upload.svg" alt="img">
                                <h4>Drag and drop a file to upload</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Order Tax</label>
                        <input type="text">
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Discount</label>
                        <input type="text">
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Shipping</label>
                        <input type="text">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control"></textarea>
                    </div>
                </div>
                <div class="col-lg-12">
                    <a href="javascript:void(0);" class="btn btn-submit me-2">Submit</a>
                    <a href="javascript:void(0);" class="btn btn-cancel">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection