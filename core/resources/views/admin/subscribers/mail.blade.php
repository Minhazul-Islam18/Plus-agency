@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">Mail Subscribers</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">Subscribers</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">Mail Subscribers</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">

            <div class="card">
                <form class="" action="{{ route('admin.subscribers.sendmail') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="card-title">Send Mail</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8 offset-lg-2">
                                <div class="form-group">
                                    <label>Send To **</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="recipient_type" value="general"
                                                class="selectgroup-input" checked>
                                            <span class="selectgroup-button">All Subscribers</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="recipient_type" value="personal"
                                                class="selectgroup-input">
                                            <span class="selectgroup-button">Specific Subscribers</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('recipient_type'))
                                        <p class="text-danger mb-0">{{ $errors->first('recipient_type') }}</p>
                                    @endif
                                </div>

                                <div class="form-group" id="subscriberPickerGroup" style="display:none;">
                                    <label>Select Subscribers **</label>
                                    @if (count($subscribers) == 0)
                                        <p class="text-warning mb-0">No subscribers yet.</p>
                                    @else
                                        <div class="subscriber-picker">
                                            <div class="subscriber-picker__toolbar">
                                                <div class="subscriber-picker__search">
                                                    <i class="fas fa-search"></i>
                                                    <input type="text" id="subscriberSearch"
                                                        placeholder="Search by email...">
                                                </div>
                                                <div class="subscriber-picker__actions">
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        id="subscriberSelectAllBtn">Select All</button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        id="subscriberClearBtn">Clear</button>
                                                    <span class="subscriber-picker__count" id="subscriberCount">0
                                                        selected</span>
                                                </div>
                                            </div>
                                            <div class="subscriber-picker__list" id="subscriberList">
                                                @foreach ($subscribers as $subscriber)
                                                    <label class="subscriber-picker__item"
                                                        data-search="{{ strtolower($subscriber->email . ' ' . $subscriber->id) }}">
                                                        <input type="checkbox" name="subscriber_ids[]"
                                                            value="{{ $subscriber->id }}" class="subscriber-check">
                                                        <span class="subscriber-picker__icon">
                                                            <i class="fas fa-envelope"></i>
                                                        </span>
                                                        <span class="subscriber-picker__info">
                                                            <span
                                                                class="subscriber-picker__title">{{ $subscriber->email }}</span>
                                                            <span class="subscriber-picker__meta">#{{ $subscriber->id }} —
                                                                {{ $subscriber->created_at ? $subscriber->created_at->format('d M Y') : '—' }}</span>
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <p class="subscriber-picker__empty text-muted mb-0" id="subscriberEmpty"
                                                style="display:none;">No subscribers match your search.</p>
                                        </div>
                                    @endif
                                    @if ($errors->has('subscriber_ids'))
                                        <p class="text-danger mb-0">{{ $errors->first('subscriber_ids') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="">Subject **</label>
                                    <input type="text" class="form-control" name="subject" value=""
                                        placeholder="Enter subject of E-mail">
                                    @if ($errors->has('subject'))
                                        <p class="text-danger mb-0">{{ $errors->first('subject') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="">Message **</label>
                                    <textarea name="message" class="summernote" data-height="150"></textarea>
                                    @if ($errors->has('message'))
                                        <p class="text-danger mb-0">{{ $errors->first('message') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button type="submit" class="btn btn-success">
                            <span class="btn-label">
                                <i class="fa fa-check"></i>
                            </span>
                            Send Mail
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <style>
        .subscriber-picker {
            border: 1px solid #1a2035;
            border-radius: 8px;
            overflow: hidden;
        }

        .subscriber-picker__toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            background: #1a2035;
            border-bottom: 1px solid #141829;
        }

        .subscriber-picker__search {
            position: relative;
            flex: 1 1 220px;
        }

        .subscriber-picker__search i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #ffffff;
            font-size: 13px;
        }

        .subscriber-picker__search input {
            width: 100%;
            padding: 6px 10px 6px 30px;
            border: 1px solid #293253;
            border-radius: 6px;
            font-size: 13px;
            background: #202940;
            color: #fff;
        }

        .subscriber-picker__actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .subscriber-picker__count {
            font-size: 12px;
            color: #6b7684;
            white-space: nowrap;
        }

        .subscriber-picker__list {
            max-height: 260px;
            overflow-y: auto;
            padding: 6px;
        }

        .subscriber-picker__item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            margin: 0 0 4px;
            border-radius: 6px;
            cursor: pointer;
        }

        .subscriber-picker__item:hover {
            background: #293253;
        }

        .subscriber-picker__item input {
            margin: 0;
            flex-shrink: 0;
        }

        .subscriber-picker__icon {
            width: 30px;
            height: 30px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #1a2035;
            color: #6b7684;
            font-size: 13px;
        }

        .subscriber-picker__info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .subscriber-picker__title {
            font-size: 13px;
            font-weight: 500;
            color: #ffffff;
        }

        .subscriber-picker__meta {
            font-size: 11px;
            color: #9aa4b2;
        }

        .subscriber-picker__empty {
            padding: 16px;
            text-align: center;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var radios = document.querySelectorAll('input[name="recipient_type"]');
            var pickerGroup = document.getElementById('subscriberPickerGroup');

            function togglePicker() {
                var selected = document.querySelector('input[name="recipient_type"]:checked');
                pickerGroup.style.display = (selected && selected.value === 'personal') ? 'block' : 'none';
            }
            radios.forEach(function(r) {
                r.addEventListener('change', togglePicker);
            });
            togglePicker();

            var search = document.getElementById('subscriberSearch');
            var items = document.querySelectorAll('.subscriber-picker__item');
            var emptyMsg = document.getElementById('subscriberEmpty');
            var countEl = document.getElementById('subscriberCount');
            var selectAllBtn = document.getElementById('subscriberSelectAllBtn');
            var clearBtn = document.getElementById('subscriberClearBtn');

            function updateCount() {
                var n = document.querySelectorAll('.subscriber-check:checked').length;
                countEl.textContent = n + ' selected';
            }

            function filterList() {
                var term = (search.value || '').toLowerCase().trim();
                var visibleCount = 0;
                items.forEach(function(item) {
                    var match = !term || item.getAttribute('data-search').indexOf(term) !== -1;
                    item.style.display = match ? 'flex' : 'none';
                    if (match) visibleCount++;
                });
                emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
            }

            if (search) search.addEventListener('input', filterList);

            document.querySelectorAll('.subscriber-check').forEach(function(cb) {
                cb.addEventListener('change', updateCount);
            });

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    items.forEach(function(item) {
                        if (item.style.display !== 'none') {
                            item.querySelector('.subscriber-check').checked = true;
                        }
                    });
                    updateCount();
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    document.querySelectorAll('.subscriber-check').forEach(function(cb) {
                        cb.checked = false;
                    });
                    updateCount();
                });
            }

            updateCount();
        });
    </script>
@endsection
