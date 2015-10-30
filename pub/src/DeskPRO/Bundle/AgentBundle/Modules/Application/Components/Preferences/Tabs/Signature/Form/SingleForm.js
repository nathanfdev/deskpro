import React from 'react';

export class SingleForm extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      signature: ''
    };
  }

  onChange = event => {
    this.setState({
      signature: event.target.value
    });
  };

  render() {
    return (
      <section className="single-signature" id="single-signature">
        <p>This signature will be appended automatically when you send ticket replies. Any reply drafts will be cleared when updating your signature.</p>

        <div className="signature">
          <form>
            <div className="textarea-tagalong"></div>
              <textarea placeholder="Your Signature"
                        value={this.state.signature}
                        onChange={this.onChange} />

            <input type="submit" value="Save Signature" />
          </form>
        </div>
      </section>
    );
  }
}
