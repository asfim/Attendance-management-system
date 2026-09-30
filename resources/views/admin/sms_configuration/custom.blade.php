@extends('layouts.app')

@section('content')
<div class="row justify-content-center g-4">
    <div class="col-md-8">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-comment-sms text-primary me-2"></i>Send Custom SMS</h5>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('admin.sms.custom.send') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary">Search Guardian / Student</label>
                    <select name="phones[]" id="studentSearch" class="form-select" multiple="multiple" required>
                    </select>
                    <div class="form-text mt-2 text-muted">
                        <i class="fa-solid fa-info-circle me-1"></i> Type name, roll number, or phone to find the guardian's number. You can select multiple recipients. 
                        If you want to send to a new number directly, you can also type it and press Enter.
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary">Message <span class="text-danger">*</span></label>
                    <textarea name="message" class="form-control" rows="5" placeholder="Type your SMS message here..." required></textarea>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted" id="charCount">0 characters</small>
                        <small class="text-muted" id="smsCount">0 SMS</small>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fa-solid fa-paper-plane me-2"></i>Send SMS
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--multiple {
        min-height: 42px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        padding-left: 5px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #f8f9fa;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 4px 8px;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    [data-bs-theme="dark"] .select2-container--default .select2-selection--multiple {
        background-color: transparent;
        border-color: #334155;
    }
    [data-bs-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #1e293b;
        border-color: #475569;
        color: #f8f9fa;
    }
    [data-bs-theme="dark"] .select2-dropdown {
        background-color: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }
    [data-bs-theme="dark"] .select2-search input {
        background-color: #0f172a;
        color: #fff;
        border-color: #334155;
    }
    .select2-result-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .select2-result-item img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }
    .select2-selection__choice img {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        object-fit: cover;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        function formatResult(item) {
            if (!item.id) {
                return item.text;
            }
            if (item.newTag) {
                return $('<span><i class="fa-solid fa-phone me-2"></i>' + item.text + '</span>');
            }
            var $container = $(
                '<div class="select2-result-item">' +
                    '<img src="' + item.image + '" alt="avatar">' +
                    '<div>' +
                        '<div class="fw-bold">' + item.text.split(' | ')[0] + '</div>' +
                        '<div class="text-muted fs-8">' + (item.text.split(' | ')[1] || '') + '</div>' +
                    '</div>' +
                '</div>'
            );
            return $container;
        }

        function formatSelection(item) {
            if (!item.id) {
                return item.text;
            }
            if (item.newTag || !item.image) {
                return item.text;
            }
            var $container = $(
                '<span>' +
                    '<img src="' + item.image + '" class="me-1">' +
                    item.text.split(' | ')[0] +
                '</span>'
            );
            return $container;
        }

        $('#studentSearch').select2({
            placeholder: 'Search by student name, roll, class or guardian phone...',
            tags: true, 
            multiple: true,
            templateResult: formatResult,
            templateSelection: formatSelection,
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                if(term.match(/^[0-9+]+$/)) {
                    return {
                        id: term,
                        text: 'Use number: ' + term,
                        newTag: true 
                    }
                }
                return null;
            },
            ajax: {
                url: '{{ route("admin.sms.search-student") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term // search term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            minimumInputLength: 2
        });

        // Character counter for SMS
        $('textarea[name="message"]').on('input', function() {
            var len = $(this).val().length;
            var smsParts = Math.ceil(len / 160) || 1;
            
            // Adjust for unicode (Bengali)
            var hasUnicode = /[^\u0000-\u00ff]/.test($(this).val());
            if (hasUnicode) {
                smsParts = Math.ceil(len / 70) || 1;
            }

            $('#charCount').text(len + ' characters');
            $('#smsCount').text(smsParts + ' SMS (' + (hasUnicode ? 'Unicode' : 'Text') + ')');
        });

    });
</script>
@endpush
