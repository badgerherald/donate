import { Component, h, State, Prop, Event, EventEmitter } from '@stencil/core';
import { parseConfigFileTextToJson } from 'typescript';

declare const exa: any
@Component({
  tag: 'exa-donate-checkout',
  styleUrl: 'exa-donate-checkout.scss'
})
export class ExaDonateCheckout {

  @Prop() amount: number = 0;
  @State() streetPlaceholder
  @State() saving = false

  @State() done = false

  @Event() changeAmount : EventEmitter
  @Event() checkoutFinished : EventEmitter

  private stripe = Stripe('pk_test_Q8vFrqj9PDgGwvSKYFXqy71200pcZ4d5X5');

  private cardRef 
  private stripeCard = this.stripe.elements().create('card')
  private form

  private firstNameField
  private lastNameField
  private emailField
  private phoneField
  private commentField

  private streetField 
  private aptField 
  private yearField
  private cityField 
  private countryField 
  private zipField

  componentDidLoad() {
    this.stripeCard.mount(this.cardRef)
    this.stripeCard.update({})
  }

  yearChanged() {
    if(this.yearField.value && this.yearField.value < 2014) {
      this.streetField.placeholder = "326 W Gorham St"
    } else {

    }
  }

  async submit(event) {
    event.preventDefault();

    if(this.saving) {
      return
    }

    this.saving = true;

    const {token, error} = await this.stripe.createToken(this.stripeCard, {
      
    });


    if (error) {
      // Inform the customer that there was an error.
      const errorElement = document.getElementById('card-errors');
      errorElement.textContent = error.message;
      this.saving = false;
      return 
    } else {

      this.saving = true;

      const hiddenInput = document.createElement('input');
      hiddenInput.setAttribute('type', 'hidden');
      hiddenInput.setAttribute('name', 'stripeToken');
      hiddenInput.setAttribute('value', token.id);
      this.form.appendChild(hiddenInput);

      // Submit the form
      await this.processDonation(token)
    }
  }

  async processDonation(token) {
    return fetch(exa.api_url + "/hexa/v1/process-donation", {
      method: 'POST', // *GET, POST, PUT, DELETE, etc.
      cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
      headers: {
          'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        amount: 42.00,
        token: token.id,
        nonce: "asd",
        email: this.emailField.value,
        phone: this.phoneField.value,
        year: this.yearField.value,
        comment: this.commentField.value,
        street: this.streetField.value,
        apt: this.aptField.value,
        city: this.cityField.value,
        country: this.countryField.value,
        zip: this.zipField.value,
      }),
    }).then(response => response.json()).then(json => {
      if(json.success) {
        this.done = true;
      }
    }); // parses JSON response into native JavaScript objects 
  }

  validate(): boolean {
    if(this.firstNameField.value.count < 0) {
      return false;
    }
    if(this.lastNameField.value.count < 0) {
      return false;
    }
  }

  allRequiredFieldsFilled() {
    var allComplete = true;
    [].forEach(this.form.querySelectorAll("[required]"), element => {
      if( (element as HTMLInputElement).value.length === 0 ) {
        allComplete = false;
      }
    })

    return allComplete;
  }

  runValidations() {
    this.form.classList.add("validations")
  }

  emitEditAmount() {
    this.changeAmount.emit()
  }

  render() {
    if(this.done) {
      return <div class="thank-you"><h3>Thank you! 🎉</h3> <p>In the future, this will also send an email reciept. And look better. <a class="edit" onClick={_ => this.emitEditAmount()}>Start Over</a></p></div>
    }
    return [
    <div class="amount-banner">
      <span class="amount">${this.amount}<span class="clap"></span></span><br />
      <a class="edit" onClick={_ => this.emitEditAmount()}>Change Amount</a>
    </div>,
    <form ref={r => this.form = r} onSubmit={e => this.submit(e)} >
      <div class="form-section">
        <h4>Alumni Details</h4>
        <span>
          <label>First Name *</label>
          <input required={true} name="first-name" type="text" placeholder="Carl (required)" ref={ref => this.firstNameField = ref} />
        </span>
        <span>
          <label>Last Name *</label>
          <input required={true} name="last-name" type="text" placeholder="Golden (required)" ref={ref => this.lastNameField = ref} />
        </span>
        <span>
          <label>Email *</label>
          <input required={true} name="email" type="text" placeholder="cgolden@example.com (required)" ref={ref => this.emailField = ref} />
        </span>
        <span>
          <label>Phone # *</label>
          <input required={true} name="phone" type="text" placeholder="608-257-4809 (required)" ref={ref => this.phoneField = ref} />
        </span>
        <span class="col2">
          <label>Final Year *</label>
          <input required={true} name="final-year" type="text" placeholder="1969 (required)" onChange={_ => this.yearChanged() } ref={ ref=>this.yearField = ref} />
        </span>
        <span class="col4">
          <label>Comment</label>
          <input name="comment" type="text" ref={ref => this.commentField = ref}/>
        </span>
        <div class="clearfix"></div>
      </div>

      <div class="form-section">
        <h4>Mailing Address</h4>
        <span class="col4">
          <label>Street *</label>
          <input required={true} name="street" type="text" placeholder="" ref ={ ref=>this.streetField = ref}/>
        </span>
        <span class="col2">
          <label>Apt/No</label>
          <input name="unit" type="text" placeholder="201" ref ={ ref=>this.aptField = ref} />
        </span>
        <span class="col2">
          <label>City *</label>
          <input required={true} name="city" type="text" placeholder="Madison" ref={ ref => this.cityField = ref } />
        </span>
        <span class="col2">
          <label>Country *</label>
          <input required={true} name="country" type="text" placeholder="United States" ref={ ref => this.countryField = ref } />
        </span>
        <span class="col2">
          <label>Zip *</label>
          <input required={true} name="zip" type="text" placeholder="53706" ref={ ref => this.zipField = ref } />
        </span>
        <div class="clearfix"></div>
      </div>
      
      <div class="form-section">
        <h4>Payment</h4>
        <span class="stripe" ref={ref => this.cardRef = ref} />
        <div class="clearfix"></div>
      </div>
      <input type="submit" value={this.saving ? "saving..." : "submit"} onMouseUp={_ => this.runValidations()} />
    </form>
    ]
  }
}
