import { Component, h, State, Prop, Event, EventEmitter } from "@stencil/core"

declare const exa: any
@Component({
	tag: "exa-donate-checkout",
	styleUrl: "exa-donate-checkout.scss",
})
export class ExaDonateCheckout {
	@Prop() pk: string
	@Prop() n: string
	@Prop() rk: string
	@Prop() ht: string

	@Prop() serverError
	@Prop() amount: number = 0
	@Prop() reoccuring: number
	@State() streetPlaceholder
	@State() saving = false

	@State() done = false

	@Event() changeAmount: EventEmitter
	@Event() checkoutFinished: EventEmitter

	private stripe

	private cardRef
	private buttonRef
	private stripeCard
	private stripeButton
	private form

	private firstNameField
	private lastNameField
	private emailField
	private commentField

	private paymentRequest

	componentWillLoad() {
		this.stripe = Stripe(this.pk)
		this.stripeCard = this.stripe.elements().create("card")

		this.paymentRequest = this.stripe.paymentRequest({
			country: "US",
			currency: "usd",
			total: {
				label: "Demo total",
				amount: 1099,
			},
			requestPayerName: true,
			requestPayerEmail: true,
		})
		this.stripeButton = this.stripe.elements().create("paymentRequestButton", {
			paymentRequest: this.paymentRequest,
		})
	}

	componentDidRender() {
		this.stripeCard.mount(this.cardRef)
		this.stripeCard.update({})
		this.paymentRequest.canMakePayment().then((res) => {
			if (res) {
				this.stripeButton.mount(this.buttonRef)
			} else {
			}
		})
	}

	async recaptcha(): Promise<string> {
		return new Promise((res, rej) => {
			grecaptcha.ready(() =>
				grecaptcha.execute(this.rk, { action: "submit" }).then(
					(token) => res(token),
					(reason) => rej(reason)
				)
			)
		})
	}

	async submit(event) {
		event.preventDefault()

		if (this.saving) {
			return
		}

		let recaptcha = await this.recaptcha()

		this.saving = true
		const { token, error } = await this.stripe.createToken(this.stripeCard, {})

		if (error) {
			// Inform the customer that there was an error.
			const errorElement = document.getElementById("card-errors")
			if (errorElement) {
				errorElement.textContent = error.message
			}
			this.saving = false
			return
		} else {
			this.saving = true

			const hiddenInput = document.createElement("input")
			hiddenInput.setAttribute("type", "hidden")
			hiddenInput.setAttribute("name", "stripeToken")
			hiddenInput.setAttribute("value", token.id)
			this.form.appendChild(hiddenInput)

			// Submit the form
			try {
				await this.processDonation(token, recaptcha)
			} catch (err) {
				this.serverError =
					"Something went wrong and we could not process your donation. Refreshing might help. Otherwise please reach out to publisher@badgerherald.com."
				this.saving = false
			}
		}
	}

	async processDonation(token, recaptcha) {
		return fetch("/wp-json/donate/v1/process-donation", {
			method: "POST", // *GET, POST, PUT, DELETE, etc.
			cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": this.ht,
			},
			body: JSON.stringify({
				amount: this.amount,
				first: this.firstNameField.value,
				last: this.lastNameField.value,
				reoccurance: this.reoccuring,
				token: token.id,
				nonce: this.n,
				email: this.emailField.value,
				comment: this.commentField.value,
				recaptcha: recaptcha,
			}),
		})
			.then((response) => {
				if (response.status === 400) {
					throw "error"
				}
				return response.json()
			})
			.then((json) => {
				if (json.success) {
					this.done = true
				} else {
					this.serverError = json.error
					this.saving = false
				}
			})
	}

	validate(): boolean {
		if (this.firstNameField.value.count < 0) {
			return false
		}
		if (this.lastNameField.value.count < 0) {
			return false
		}
	}

	allRequiredFieldsFilled() {
		var allComplete = true
		;[].forEach(this.form.querySelectorAll("[required]"), (element) => {
			if ((element as HTMLInputElement).value.length === 0) {
				allComplete = false
			}
		})

		return allComplete
	}

	runValidations() {
		this.form.classList.add("validations")
	}

	emitEditAmount() {
		this.changeAmount.emit()
	}

	render() {
		if (this.done) {
			return (
				<div class="thank-you">
					<h3>Thank you! 🎉</h3>{" "}
					<p>
						Thank you for your generous donation to The Badger Herald. Please
						check your email for a reciept. <br />
						<a class="edit" onClick={(_) => this.emitEditAmount()}>
							Start Over
						</a>
					</p>
				</div>
			)
		}
		var details
		if (this.reoccuring > 0) {
			details = [
				<br />,
				<span class="reocurring">
					{this.reoccuring == 2 ? "each semester" : ""}
					{this.reoccuring == 12 ? "monthly" : ""}
				</span>,
				<br />,
				<span class="details">
					{this.reoccuring == 2
						? "Charged October " +
						  new Date().getDate() +
						  " and April " +
						  new Date().getDate()
						: ""}
					{this.reoccuring == 12
						? "Charged on the " + new Date().getDate() + "th of each month"
						: ""}
				</span>,
			]
		}
		return [
			<div class="amount-banner">
				<span class="dollar">$</span>
				<span class="amount">{this.amount}</span>
				{details}
				<br />
				<a class="edit" onClick={(_) => this.emitEditAmount()}>
					change amount
				</a>
			</div>,
			<form ref={(r) => (this.form = r)} onSubmit={(e) => this.submit(e)}>
				<div class="form-section">
					<h4>1. Contact Information</h4>
					<span>
						<label>First Name *</label>
						<input
							required={true}
							name="first"
							type="text"
							placeholder="Carl (required)"
							ref={(ref) => (this.firstNameField = ref)}
						/>
					</span>
					<span>
						<label>Last Name *</label>
						<input
							required={true}
							name="last"
							type="text"
							placeholder="Golden (required)"
							ref={(ref) => (this.lastNameField = ref)}
						/>
					</span>
					<span class="col4">
						<label>Email *</label>
						<input
							required={true}
							name="email"
							type="text"
							placeholder="cgolden@example.com (required)"
							ref={(ref) => (this.emailField = ref)}
						/>
					</span>
					<span class="col4">
						<label>Comment</label>
						<input
							name="comment"
							type="text"
							ref={(ref) => (this.commentField = ref)}
						/>
					</span>
					<div class="clearfix"></div>
				</div>

				<div class="form-section">
					<h4>2. Payment</h4>
					{this.serverError ? (
						<div id="card-errors">{this.serverError}</div>
					) : (
						""
					)}
					<span class="stripe" ref={(ref) => (this.cardRef = ref)} />
					<span class="button-pay" ref={(ref) => (this.buttonRef = ref)} />
					<div class="clearfix"></div>
				</div>
				<input
					type="submit"
					value={
						this.saving
							? "saving..."
							: "Donate $" +
							  this.amount +
							  (this.reoccuring == 2 ? " Each Semester" : "") +
							  (this.reoccuring == 12 ? " Each Month" : "")
					}
					onMouseUp={(_) => this.runValidations()}
				/>
			</form>,
		]
	}
}
