import { Component, State, h, EventEmitter, Event } from '@stencil/core';

@Component({
  tag: 'exa-donate-amount',
  styleUrl: 'exa-donate-amount.scss'
})
export class ExaDonateAmount {

  private defaults = [10, 25, 50, 75, 125, 250, 500] 
  
  @State() amount : number
  @State() isCustomAmount : boolean = false;
  @State() isTyping : boolean

  @Event() amountChanged : EventEmitter<number>

  renderAmountSection() {
    return [
      this.renderAmountGrid()
    ]
  }

  render() {
    return this.renderAmountGrid();
  }

  private setAmount(amount, custom, emit) {
    this.amount = amount 
    this.isCustomAmount = amount > 0 && custom 
    if(emit) {
      this.amountChanged.emit(amount)
    }
  }

  private renderAmountGrid() {
    return [
      <ul>
        <li>Monthly</li>
        <li>Each Semester</li>
        <li>One Time</li>
      </ul>,
      <ul>
        {
          this.defaults.map(amount => 
            <li class="suggestion"><a class={this.amount === amount && !this.isCustomAmount ? "current" : ""} onClick={_ => this.setAmount(amount,false,true)}>${amount}</a></li>
          )
        }
        <li class="suggestion">
          <span class={((this.amount > 0 && this.isCustomAmount) || this.isTyping) ? "current custom" : "custom"} >
            <input type="number" value={this.isCustomAmount ? this.amount : ""} placeholder="Custom" onFocus={_ => {this.isTyping = true; this.isCustomAmount = true} } onBlur={e => {this.isTyping = false; this.setAmount(parseInt((e.target as HTMLInputElement).value,10),true,true)} } />
          </span>
          {this.isCustomAmount || this.isTyping ? <button class="button"><span class="hidden">Submit</span></button> : undefined}
        </li>
        
      </ul>,
      <div class="clearfix"></div>,
      
    ]
  }
}
