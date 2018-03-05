import React from 'react';
import { storiesOf, action, linkTo } from '@storybook/react';
import Isvg from 'react-inlinesvg';
import logoSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/logo.svg';
import { Login } from 'DeskPRO/Bundle/DemoBundle/Modules/Login/Components/Login';
import { ForgottenPassword } from 'DeskPRO/Bundle/DemoBundle/Modules/Login/Components/ForgottenPassword';
import { ConfirmExtend } from 'DeskPRO/Bundle/DemoBundle/Modules/Login/Components/ConfirmExtend';
import { ConfirmReset } from 'DeskPRO/Bundle/DemoBundle/Modules/Login/Components/ConfirmReset';
import { DeleteAccountFeedback } from 'DeskPRO/Bundle/DemoBundle/Modules/Login/Components/DeleteAccountFeedback';
import ExtendTrial from 'DeskPRO/Bundle/DemoBundle/Modules/Login/Components/ExtendTrial';
import { demoCss, redux } from '../../../decorators';

storiesOf('Demo: expired demo', module)
  .addDecorator(story =>
    <div className="container">
      <div className="logo">
        <Isvg src={logoSvg} />
      </div>
      {story()}
    </div>
  )
  .addDecorator(story => demoCss(story()))
  .addDecorator(story => redux({}, story()))
  .add(
    'Login', () =>
      <Login
        onForgotPassword={linkTo('Demo: expired demo', 'Forgotten Password')}
        onLogin={linkTo('Demo: expired demo', 'Extend your trial')}
      />
  )
  .add(
    'Forgotten Password', () =>
      <ForgottenPassword
        onBackToLogin={linkTo('Demo: expired demo', 'Login')}
        onEmailInstructions={action('Email instructions')}
      />
  )
  .add(
    'Extend your trial', () =>
      <ExtendTrial
        onResumeTrial={linkTo('Demo: expired demo', 'Demo Extended')}
        onDeleteAccount={linkTo('Demo: expired demo', 'Delete account feedback')}
      />
  )
  .add(
    'Demo Extended', () =>
      <ConfirmExtend
        onPreserveData={action('Preserve data')}
        onDeleteData={linkTo('Demo: expired demo', 'Confirm reset')}
      />
  )
  .add(
    'Confirm reset', () =>
      <ConfirmReset
        onReset={action('Confirm reset')}
        onCancelButton={linkTo('Demo: expired demo', 'Demo Extended')}
      />
  )
  .add(
    'Delete account feedback', () =>
      <DeleteAccountFeedback
        onSubmit={action('Submit')}
      />
  )
;
