<form action="{{route('customer.exportFile')}}" method="get">
    <input type="text" name="input">
    <button type="submit">lsa</button>
</form>
<a href="{{route('customer.exportFile')}}">Export File</a>

<table class="table table-striped">
{{--            <thead>--}}
{{--            <th>ID</th>--}}
{{--            <th>Name</th>--}}
{{--            <th>Last Name</th>--}}
{{--            </thead>--}}
{{--            <tbody>--}}
{{--            @foreach($customers as $item)--}}
{{--                <tr>--}}
{{--                    <td>{{$item->id}}</td>--}}
{{--                    <td>{{$item->first_name}}</td>--}}
{{--                    <td>{{$item->last_name}}</td>--}}
{{--                </tr>--}}
{{--            @endforeach--}}
{{--            </tbody>--}}
    <div data-v-5a45470d="" class="sticky top-5" style="display: flex; flex-direction: column; gap: 20px;">
        <div data-v-5a45470d="" class="preview-content" style="border-radius: 3px;"><img data-v-5a45470d="" src=""
                                                                                         alt=""
                                                                                         style="width: 100%; object-fit: cover;">
            <div data-v-5a45470d="" class="preview-email-content"
                 style="background: rgb(255, 255, 255); padding: 28px 30px 36px; display: flex; flex-direction: column; gap: 30px; border-radius: 3px; color: rgb(40, 41, 61);">
                <div data-v-5a45470d="" class="email--content" style="line-break: anywhere;"><p></p></div>
                <button data-v-5a45470d=""
                        style="width: 100%; line-height: 18px; font-size: 14px; font-weight: bold; padding: 9px 0px; background: rgb(0, 48, 132); color: rgb(255, 255, 255); border-radius: 4px;">
                    TRY FREE NOW
                </button>
            </div>
        </div>
        <div data-v-5a45470d="" class="preview--footer"
             style="font-size: 14px; font-weight: 400; line-height: 18px; color: rgb(85, 87, 112);">
            <div data-v-5a45470d=""><p style="text-align: center">Copyright 2010-2022 Firegroup, all rights
                    reserved.</p></div>
            <a data-v-5a45470d="" href="#" style="display: block; text-align: center; text-decoration: underline;">Unsubscribe
                here</a></div>
    </div>

</table>
