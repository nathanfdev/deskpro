import React from 'react';
import { storiesOf, action, linkTo } from '@kadira/storybook';
import Isvg from 'react-inlinesvg';
import logoSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/logo.svg';
import { demoCss } from 'Visual/decorators';
import { Login } from 'DeskPRO/Bundle/DemoBundle/Modules/Login/Components/Login';
import { ForgottenPassword } from 'DeskPRO/Bundle/DemoBundle/Modules/Login/Components/ForgottenPassword';
import { ConfirmExtend } from 'DeskPRO/Bundle/DemoBundle/Modules/Login/Components/ConfirmExtend';
import { ConfirmReset } from 'DeskPRO/Bundle/DemoBundle/Modules/Login/Components/ConfirmReset';
import { DeleteAccountFeedback } from 'DeskPRO/Bundle/DemoBundle/Modules/Login/Components/DeleteAccountFeedback';

storiesOf('Demo: expired demo', module)
  .addDecorator(story => demoCss(story()))
  .addDecorator(story =>
      <div className="container">
        <div className="logo">
          <Isvg src={logoSvg} />
        </div>
        {story()}
      </div>
  )
  .add(
    'Login',
    () =>
      <Login onForgotPassword={linkTo('Demo: expired demo', 'Forgotten Password')} />
  )
  .add(
    'Forgotten Password',
    () =>
      <ForgottenPassword onBackToLogin={linkTo('Demo: expired demo', 'Login')} />
  )
  .add(
    'Demo Extended',
    () =>
      <ConfirmExtend
        onPreserveData={action('Preserve data')}
        onDeleteData={linkTo('Demo: expired demo', 'Confirm reset')}
      />
  )
  .add(
    'Confirm reset',
    () =>
      <ConfirmReset
        onReset={action('Confirm reset')}
        onCancelButton={linkTo('Demo: expired demo', 'Demo Extended')}
      />
  )
  .add(
    'Delete account feedback',
    () =>
      <DeleteAccountFeedback
        onSubmit={action('Submit')}
      />
  )
;
