@extends('layouts.admin')

@section('title', 'Send Notice - ' . $member->name)
@section('page_title', 'Send Notice to: ' . $member->name)

@section('header_actions')
    <a href="{{ route('admin.staff.show', $member->id) }}" class="flex items-center space-x-1.5 px-3 py-1.5 bg-muted hover:bg-muted text-foreground hover:text-foreground rounded-lg text-xs font-semibold transition-all border border-border shadow-sm">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        <span>Back to Profile</span>
    </a>
@endsection

@section('content')
<div x-data="{ 
    previewMode: false,
    noticeType: 'General Notice',
    subject: '',
    message: '',
    deliveryMethod: 'email',
    updatePreview() {
        this.subject = $refs.subjectInput.value;
        // TinyMCE integration
        if (window.tinymce && tinymce.activeEditor) {
            this.message = tinymce.activeEditor.getContent();
        } else {
            this.message = $refs.messageTextarea.value;
        }
    }
}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Form Column -->
    <div class="lg:col-span-2 bg-card border border-border rounded-xl p-6 shadow-sm">
        <div class="mb-6">
            <h3 class="font-bold text-foreground text-base">New Staff Notification / slip</h3>
            <p class="text-xs text-muted-foreground">Dispatch alerts, salary slips, or formal write-ups directly to staff channels.</p>
        </div>

        <form action="{{ route('admin.staff.notice.send', $member->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Notice Type -->
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Notice Type</label>
                    <select name="type" x-model="noticeType"
                            class="w-full p-3 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all cursor-pointer font-semibold text-foreground">
                        <option value="General Notice">General Notice</option>
                        <option value="Warning">Official Warning</option>
                        <option value="Salary Slip">Salary Slip</option>
                        <option value="Promotion">Promotion Letter</option>
                        <option value="Performance Review">Performance Review</option>
                        <option value="Bonus">Bonus / Incentive</option>
                        <option value="Suspension Notice">Suspension Notice</option>
                        <option value="Termination Notice">Termination Notice</option>
                    </select>
                </div>

                <!-- Delivery Channel -->
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Delivery Channel</label>
                    <select name="delivery_method" x-model="deliveryMethod"
                            class="w-full p-3 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all cursor-pointer font-semibold text-foreground">
                        <option value="email">Email Only</option>
                        <option value="whatsapp">WhatsApp Only</option>
                        <option value="both">Both (Email & WhatsApp)</option>
                        <option value="alert">Website Alert Only</option>
                        <option value="email_alert">Email & Website Alert</option>
                        <option value="all">All Channels (Email, WA & Alert)</option>
                    </select>
                </div>
            </div>

            <!-- Subject -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Subject Line</label>
                <input type="text" name="subject" x-ref="subjectInput" @input="updatePreview()" placeholder="Enter message subject" required
                       class="w-full p-3 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all font-semibold text-foreground">
            </div>

            <!-- Rich Text Editor (TinyMCE) -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Message Body</label>
                <textarea name="message" x-ref="messageTextarea" @input="updatePreview()" class="tinymce-editor w-full p-3 border border-border rounded-lg bg-muted focus:bg-card min-h-[300px]"></textarea>
            </div>

            <!-- Optional Attachment -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">PDF Attachment (Optional)</label>
                <div class="relative border-2 border-dashed border-border rounded-xl p-4 bg-muted hover:bg-muted transition-all flex flex-col items-center justify-center cursor-pointer text-muted-foreground">
                    <input type="file" name="pdf_file" accept=".pdf" class="absolute inset-0 opacity-0 cursor-pointer">
                    <i data-lucide="file-up" class="w-6 h-6 text-muted-foreground mb-1"></i>
                    <span class="text-xs font-semibold">Click to select PDF file (Max 10MB)</span>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                <button type="button" @click="updatePreview(); previewMode = !previewMode"
                        class="px-4 py-2.5 border border-border text-muted-foreground hover:bg-muted rounded-lg text-xs font-semibold transition-all">
                    <span x-text="previewMode ? 'Edit Form' : 'Preview Notice'"></span>
                </button>
                <button type="submit" class="bg-primary text-primary-foreground hover:bg-primary/90 hover:bg-primary/90 hover:text-primary-foreground font-medium px-6 py-2.5 rounded-lg text-xs transition-all shadow-sm flex items-center gap-1.5">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Send Dispatch</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Preview Column -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-card border border-border rounded-xl p-6 shadow-sm min-h-[400px] flex flex-col">
            <div class="border-b border-border pb-3 mb-4 flex items-center justify-between">
                <h4 class="font-bold text-foreground text-sm">Live Dispatch Preview</h4>
                <span class="text-[10px] bg-amber-50 text-amber-705 border border-amber-200/60 font-bold px-2 py-0.5 rounded-full">Preview</span>
            </div>

            <!-- Empty State Preview -->
            <div x-show="!subject && !message" class="flex-1 flex flex-col items-center justify-center text-center text-muted-foreground">
                <i data-lucide="eye" class="w-8 h-8 mb-2 text-muted-foreground"></i>
                <p class="text-xs font-semibold">Fill out the form details to visualize the dispatch layout here.</p>
            </div>

            <!-- Rendered Preview -->
            <div x-show="subject || message" class="flex-1 flex flex-col justify-between space-y-4 text-xs">
                <div class="p-4 bg-muted border border-border rounded-xl space-y-3">
                    <div>
                        <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">Notice Type</span>
                        <span class="text-xs font-bold text-foreground block" x-text="noticeType"></span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">To Staff</span>
                        <span class="text-xs font-bold text-foreground block">{{ $member->name }} ({{ $member->email }})</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">Delivery Channels</span>
                        <span class="text-xs font-semibold text-muted-foreground block" x-text="deliveryMethod.toUpperCase()"></span>
                    </div>
                    <div class="border-t border-border pt-3">
                        <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">Subject</span>
                        <p class="text-xs font-bold text-foreground" x-text="subject || '(No Subject Line Specified)'"></p>
                    </div>
                    <div class="border-t border-border pt-3 min-h-[150px]">
                        <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-2">Message HTML</span>
                        <div class="prose max-w-none text-[11px] text-foreground bg-card border border-border p-3 rounded-lg overflow-y-auto max-h-[200px]" x-html="message || '<em>No message body added yet.</em>'"></div>
                    </div>
                </div>
                
                <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl text-[10px] text-blue-700 font-semibold flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                    <span>Pre-flight checks verified. PDF attachments are encrypted in transit.</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
