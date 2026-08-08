@extends('admin.layout')

@php
    $kwIsRtl = (!empty($la) && $la->rtl == 1) || (empty($la) && $be->default_language_direction == 'rtl');
@endphp

@section('styles')
    <style>
        @if ($kwIsRtl)
            form input {
                direction: rtl;
            }
        @endif
        .kw-toolbar {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #fff;
            padding: 18px 0 14px;
            border-bottom: 1px solid #eceff4;
            margin-bottom: 18px;
        }

        .kw-count {
            font-size: 13px;
            color: #8a94a6;
        }

        .kw-count b {
            color: #3b4863;
        }

        .kw-jump {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 14px;
        }

        .kw-jump-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 30px;
            padding: 0 8px;
            border-radius: 6px;
            border: 1px solid #e3e7ee;
            background: #f8f9fc;
            color: #5c6884;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
            transition: background .15s ease, color .15s ease, border-color .15s ease;
        }

        .kw-jump-btn:hover {
            background: #eef1f8;
        }

        .kw-jump-btn.is-open {
            background: #eef4ff;
            border-color: #b9d2ff;
            color: #2a5fd6;
        }

        .kw-group {
            border: 1px solid #eceff4;
            border-radius: 8px;
            margin-bottom: 10px;
            overflow: hidden;
        }

        .kw-group-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: #f8f9fc;
            cursor: pointer;
            user-select: none;
        }

        .kw-group-head:hover {
            background: #f1f4fa;
        }

        .kw-group-letter {
            font-size: 14px;
            font-weight: 700;
            color: #3b4863;
            width: 22px;
        }

        .kw-group-badge {
            font-size: 11.5px;
            font-weight: 600;
            color: #8a94a6;
            background: #eceff4;
            border-radius: 20px;
            padding: 2px 9px;
        }

        .kw-group-chevron {
            margin-left: auto;
            color: #b3bccf;
            transition: transform .15s ease;
        }

        .kw-group.is-open .kw-group-chevron {
            transform: rotate(90deg);
        }

        .kw-group-body {
            padding: 18px 16px 6px;
        }

        .kw-empty {
            padding: 60px 0;
        }

        /* Admin dark-mode skin (body[data-background-color="dark"], Atlantis
                 theme) — card bg #202940 / page bg #1a2035 / border rgba(255,255,255,.1)
                 / muted text #8b92a9, matching the theme's own dark palette exactly
                 instead of leaving these components on their light defaults. */
        body[data-background-color="dark"] .kw-toolbar {
            background: #202940;
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        body[data-background-color="dark"] .kw-count {
            color: #8b92a9;
        }

        body[data-background-color="dark"] .kw-count b {
            color: #fff;
        }

        body[data-background-color="dark"] .kw-jump-btn {
            background: #1a2035;
            border-color: rgba(255, 255, 255, 0.1);
            color: #8b92a9;
        }

        body[data-background-color="dark"] .kw-jump-btn:hover {
            background: #263049;
            color: #fff;
        }

        body[data-background-color="dark"] .kw-jump-btn.is-open {
            background: rgba(37, 208, 111, 0.12);
            border-color: rgba(37, 208, 111, 0.4);
            color: #25d06f;
        }

        body[data-background-color="dark"] .kw-group {
            background: #202940;
            border-color: rgba(255, 255, 255, 0.1);
        }

        body[data-background-color="dark"] .kw-group-head {
            background: #1a2035;
        }

        body[data-background-color="dark"] .kw-group-head:hover {
            background: #202940;
        }

        body[data-background-color="dark"] .kw-group-letter {
            color: #fff;
        }

        body[data-background-color="dark"] .kw-group-badge {
            background: rgba(255, 255, 255, 0.08);
            color: #8b92a9;
        }

        body[data-background-color="dark"] .kw-group-chevron {
            color: #6d7590;
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <h4 class="page-title">Edit Keyword</h4>
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
                <a href="#">Language Management</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">Edit Keyword</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">Edit Language Keyword</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block" href="{{ route('admin.language.index') }}">
                        <span class="btn-label">
                            <i class="fas fa-backward" style="font-size: 12px;"></i>
                        </span>
                        Back
                    </a>
                </div>
                <div class="card-body pt-0 pb-5" id="app">
                    <div class="row">
                        <div class="col-lg-12">
                            <form method="post"
                                action="{{ !empty($la) ? route('admin.language.updateKeyword', $la->id) : route('admin.language.updateKeyword', 0) }}"
                                id="langForm">
                                {{ csrf_field() }}

                                <div class="kw-toolbar">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <input type="text" v-model="search" class="form-control form-control-lg"
                                                placeholder="Search phrases…">
                                        </div>
                                        <div class="col-md-6 text-md-right mt-2 mt-md-0">
                                            <span class="kw-count"><b>@{{ filteredCount }}</b> of
                                                <b>@{{ totalCount }}</b> phrases</span>
                                        </div>
                                    </div>
                                    <div class="kw-jump">
                                        <span v-for="group in visibleGroups" :key="group.letter" class="kw-jump-btn"
                                            :class="{ 'is-open': isOpen(group.letter) }"
                                            @click="jumpTo(group.letter)">@{{ group.letter }}</span>
                                    </div>
                                </div>

                                {{-- Every key stays in the DOM (v-show, not v-for-filtered) so a
                   search term never removes an input from the submitted form
                   — updateKeyword() rewrites the whole JSON file from
                   whatever "keys[]" fields are POSTed, so a missing input
                   would silently delete that phrase from the language file. --}}
                                <div v-for="group in allGroupedKeys" :key="group.letter" :id="'kw-group-' + group.letter"
                                    class="kw-group" :class="{ 'is-open': isOpen(group.letter) }"
                                    v-show="groupMatches(group)">
                                    <div class="kw-group-head" @click="toggleGroup(group.letter)">
                                        <span class="kw-group-letter">@{{ group.letter }}</span>
                                        <span class="kw-group-badge">@{{ group.keys.length }}</span>
                                        <i class="fas fa-chevron-right kw-group-chevron"></i>
                                    </div>
                                    <div class="kw-group-body" v-show="isOpen(group.letter)">
                                        <div class="row">
                                            <div class="col-md-4 mt-2" v-for="key in group.keys" :key="key"
                                                v-show="matches(key)">
                                                <div class="form-group">
                                                    <label class="control-label"
                                                        style="white-space: normal;">@{{ key }}</label>
                                                    <div class="input-group">
                                                        <input type="text" v-model="datas[key]"
                                                            :name="'keys[' + key + ']'"
                                                            class="form-control form-control-lg">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="filteredCount === 0" class="text-center text-muted kw-empty">
                                    No phrases match "@{{ search }}".
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button type="button" class="btn btn-success"
                                    onclick="document.getElementById('langForm').submit();">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script src="{{ asset('assets/admin/js/plugin/vue/vue.js') }}"></script>
    <script src="{{ asset('assets/admin/js/plugin/vue/axios.js') }}"></script>
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>

    <script>
        window.app = new Vue({
                el: '#app',
                data: {
                    datas: {!! $json !!},
                    search: '',
                    openLetters: {}
                },
                computed: {
                    totalCount: function() {
                        return Object.keys(this.datas).length;
                    },
                    filteredCount: function() {
                        return this.filteredKeys.length;
                    },
                    filteredKeys: function() {
                        var self = this;
                        return Object.keys(this.datas).filter(function(key) {
                            return self.matches(key);
                        });
                    },
                    // Letters shown as jump chips — only ones with at least one match,
                    // so the chip row shrinks to fit the current search too.
                    visibleGroups: function() {
                        var self = this;
                        return this.allGroupedKeys.filter(function(group) {
                            return self.groupMatches(group);
                        });
                    },
                    // Stable grouping over ALL keys (unfiltered) so the DOM structure
                    // never changes as the user types — only visibility (v-show) does.
                    allGroupedKeys: function() {
                        var groups = {};
                        Object.keys(this.datas)
                            .sort(function(a, b) {
                                return a.localeCompare(b);
                            })
                            .forEach(function(key) {
                                var letter = key.charAt(0).toUpperCase();
                                if (letter < 'A' || letter > 'Z') {
                                    letter = '#';
                                }
                                if (!groups[letter]) {
                                    groups[letter] = [];
                                }
                                groups[letter].push(key);
                            });
                        return Object.keys(groups)
                            .sort()
                            .map(function(letter) {
                                return {
                                    letter: letter,
                                    keys: groups[letter]
                                };
                            });
                    }
                },
                methods: {
                    matches: function(key) {
                        var term = (this.search || '').trim().toLowerCase();
                        return !term || key.toLowerCase().indexOf(term) !== -1;
                    },
                    groupMatches: function(group) {
                        var self = this;
                        return group.keys.some(function(key) {
                            return self.matches(key);
                        });
                    },
                    isOpen: function(letter) {
                        return !!this.openLetters[letter];
                    },
                    toggleGroup: function(letter) {
                        this.$set(this.openLetters, letter, !this.openLetters[letter]);
                    },
                    jumpTo: function(letter) {
                        this.$set(this.openLetters, letter, true);
                        var el = document.getElementById('kw-group-' + letter);
                        if (el) {
                            el.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    }
                },
                watch: {
                    // Auto-expand every group that has a match while a search is active,
                    // and collapse everything back down once the search box is cleared.
                    search: function(term) {
                        var self = this;
                        if (!term.trim()) {
                            var first = this.allGroupedKeys[0];
                            this.openLetters = first ? {
                                [first.letter]: true
                            } : {};
                            return;
                        }
                        this.allGroupedKeys.forEach(function(group) {
                            if (self.groupMatches(group)) {
                                self.$set(self.openLetters, group.letter, true);
                            }
                        });
                    }
                },
                created: function() {
                    var first = this.allGroupedKeys[0];
                    if (first) {
                        this.$set(this.openLetters, first.letter, true);
                    }
                }
            })
    </script>
@endsection
