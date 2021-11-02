<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css"
      integrity="sha512-YWzhKL2whUzgiheMoBFwW8CKV4qpHQAEuvilg9FAn5VJUDwKZZxkJNuGM4XkWuk94WCrrwslk8yWNGmY1EduTA=="
      crossorigin="anonymous" referrerpolicy="no-referrer"/>
<div class="order-status order-success">
    <div class="top-part">
        @if($payment->getStatus() === \sindibad\zaincash\Payment::SUCCESS_STATUS)

            <div class="order-success">
                <i class="far fa-check-circle"></i>
                <h3>
                    Payment Successful
                    @if($payment->getOrderId())
                        <span>Order #: {{$payment->getOrderId()}}</span>
                    @endif
                </h3>
            </div>

        @else

            <div class="order-error">
                <i class="far fa-times-circle"></i>
                <h3>
                    Payment Failed
                    @if($payment->getOrderId())
                        <span>Order #: {{$payment->getOrderId()}}</span>
                    @endif
                </h3>
            </div>

        @endif

        <small>
            {{$payment->getErrorMessage()}}
        </small>


    </div>
    <ul>
        <li>
            <div>Status:</div>
            <div>{{$payment->getStatus()}}</div>
        </li>
        @if($payment->getStatus() !== \sindibad\zaincash\Payment::CANCEL_STATUS)
            <li>
                <div>Amount:</div>
                <div>{{$payment->getAmount()}}</div>
            </li>
            @if($payment->getOperationId())
                <li>
                    <div>Operation Id:</div>
                    <div>{{$payment->getOperationId()}}</div>
                </li>
            @endif
            <li>
                <div>Date:</div>
                <div>{{$payment->getInitDate()}}</div>
            </li>
        @endif
    </ul>

</div>
<div class="_row _text-center">
    <a href="{{empty($payment->getBackButtonLink()) ? '/' :  $payment->getBackButtonLink() }}" class="back-button">&laquo; {{empty($payment->getBackButtonText()) ? "Back to Home" : $payment->getBackButtonText()}}</a>
</div>

<style>
    .order-status {
        text-align: center;
        max-width: 600px;
        margin: 10px auto;
        border: 1px solid #eee;
        font-family: "Arial";
    }

    .order-status .top-part {
        border-bottom: 1px solid #eee;
    }

    .order-status .top-part i {
        font-size: 50px;
        padding-top: 15px;
    }

    .order-status .order-success {
        color: #2ca954;
    }

    .order-status .order-error {
        color: #ff0000;
    }

    .order-status .top-part h3 {
        margin-bottom: 0;
        font-weight: bold;
        margin-bottom: 15px;
    }

    .order-status .top-part small {
        font-size: 14px;
        font-weight: normal;
        display: block;
        color: #000;
        background: #f1f3f6;
        padding: 20px 40px;
    }

    .order-status .top-part h3 span {
        display: block;
        margin-top: 5px;
        color: #000;
        font-size: 16px;
        font-weight: normal;
    }

    .order-status ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }

    .order-status ul li {
        font-family: "Arial";
        display: flex;
        justify-content: space-between;
        padding: 10px;
        text-transform: capitalize;
        border-bottom: 1px solid #eee;
    }

    .order-status ul li:last-child {
        border: none;
    }

    .order-status ul li div {
        font-size: 14px;
        font-weight: normal;
        color: #444;
    }

    .order-status ul li div:last-child {
        font-weight: bold;
        color: #000;
    }

    a {
        text-decoration: none;
        display: inline-block;
        padding: 8px 16px;
    }

    a:hover {
        background-color: #ddd;
        color: black;
    }

    .back-button {
        background-color: #f1f1f1;
        color: black;
    }

    ._row {
        width: 100%;
    }

    ._text-center {
        text-align: center;
    }
</style>
