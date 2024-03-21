import { Component, State, h, Prop } from "@stencil/core"

@Component({
	tag: "bhrld-donation-form",
	styleUrl: "bhrld-donation-form.scss",
})
export class HeraldDonationForm {
	@State() amount: number
	@State() isCheckout: Boolean

	/// public API keys & nonce tokens
	@Prop() pk: string
	@Prop() rk: string

	@Prop() formTitle: string
	@Prop() subhead: string

	private checkout
	private kiosk

	@State() reoccuring = 2

	async setAmount(number) {
		this.amount = number
		this.isCheckout = true
	}

	setReoccuring(perYear) {
		this.reoccuring = perYear
	}

	// Static pieces:
	renderHeader() {
		return [
			<div class="header">
				<h3>{this.formTitle || "Donate"}</h3>
				<p>{this.subhead || "Support the Herald Experiment"}</p>
			</div>,
			<span class="charm">💙📰</span>,
		]
	}

	renderNav() {}

	renderDisclaimer() {
		return (
			<div class="disclaimer">
				<p>
					The Badger Herald is a 501c(3). All donations tax deductable. EIN
					39-1129947. See 990s{" "}
					<a href="https://projects.propublica.org/nonprofits/organizations/391129947">
						here
					</a>
					. Payments processed by Stripe.
				</p>
				<p>
					This form is protected by reCAPTCHA and the Google{" "}
					<a href="https://policies.google.com/privacy">Privacy Policy</a> and{" "}
					<a href="https://policies.google.com/terms">Terms of Service</a>{" "}
					apply.
				</p>
			</div>
		)
	}

	// Content pieces:
	renderKiosk() {
		if (this.isCheckout) {
			return
		}
		this.kiosk = (
			<exa-donate-amount
				reoccuring={this.reoccuring}
				onAmountChanged={(ev) => {
					this.setAmount(ev.detail)
				}}
				onReoccuringChanged={(ev) => this.setReoccuring(ev.detail)}
			/>
		)
		return this.kiosk
	}

	renderCheckout() {
		if (!this.isCheckout) {
			return
		}

		this.checkout = (
			<exa-donate-checkout
				pk={this.pk}
				rk={this.rk}
				reoccuring={this.reoccuring}
				amount={this.amount}
				onChangeAmount={(_) => (this.isCheckout = false)}
			/>
		)
		this.checkout.amount = this.amount
		return this.checkout
	}

	render() {
		return (
			<div class="donate-form">
				{[
					this.renderHeader(),
					this.renderNav(),
					this.renderKiosk(),
					this.renderCheckout(),
					this.renderDisclaimer(),
				]}
			</div>
		)
	}
}
