import React from 'react';
import PropTypes from 'prop-types';
import { findPhoneNumbers, getCountryCallingCode } from 'libphonenumber-js';
import { connect } from 'react-redux';
import { Icon } from '@deskpro/react-components';
import { openDialpad } from '../../Actions/clientActions';
import { canOpenDialpadSelector } from '../../Selectors/numbers';

@connect(state => ({
  canOpenDialpad: canOpenDialpadSelector(state)
}))
class MessagePhoneNumber extends React.PureComponent {
  static propTypes = {
    dispatch:       PropTypes.func,
    number:         PropTypes.string,
    children:       PropTypes.node,
    canOpenDialpad: PropTypes.bool
  };

  static detectPhoneNumbers(messages, numbers) {
    let agentCountry = null;
    if (numbers.size) {
      agentCountry = numbers.first().get('country_code').toUpperCase();
    }
    messages.find('.content-message').toArray().forEach((messageContent) => {
      let country = null;
      const personPhoneCountryEl = messageContent.getElementsByClassName('person-phone-country').item(0);
      if (personPhoneCountryEl) {
        country = personPhoneCountryEl.innerText;
      }
      if (!country) {
        const messageCountryEl = messageContent.getElementsByClassName('message-flag').item(0);
        if (messageCountryEl) {
          const matches = messageCountryEl.className.match(/dp-flag-([a-z]+)/);
          if (matches) {
            country = matches[1].toUpperCase();
          }
        }
      }
      if (!country) {
        country = agentCountry;
      }
      const message = messageContent.getElementsByClassName('body-text-message').item(0);
      let diff = 0;
      findPhoneNumbers(message.innerHTML, country).forEach((number) => {
        const initialNumber = message.innerHTML.substring(number.startsAt - diff, number.endsAt - diff);
        const intlNumber = `${getCountryCallingCode(number.country)}${number.phone}`;
        const replacement = `<span data-tel="+${intlNumber}" class="dp-click-to-call">${initialNumber}</span>`;
        message.innerHTML = message.innerHTML.substring(0, number.startsAt - diff)
          + replacement + message.innerHTML.substring(number.endsAt - diff);
        diff = diff + initialNumber.length - replacement.length;
      });
      const clickToCall = messageContent.getElementsByClassName('dp-click-to-call');
      for (let i = 0; i < clickToCall.length; i++) {
        const element = clickToCall.item(i);
        const number = element.dataset.tel;
        window.AgentLegacyBundle.renderClickToCall(element, number, element.innerText);
      }
    });
  }

  openDialpad = (e) => {
    e.preventDefault();
    e.stopPropagation();

    const { number, canOpenDialpad, dispatch } = this.props;
    if (canOpenDialpad) {
      dispatch(openDialpad(number));
    }

    return false;
  };

  render() {
    const { number, children } = this.props;
    return (
      <a onClick={this.openDialpad} href={`tel:${number}`}>
        {children} <Icon name="phone" />
      </a>
    );
  }
}

export default MessagePhoneNumber;
