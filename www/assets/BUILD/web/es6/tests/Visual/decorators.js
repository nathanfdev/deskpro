import React from 'react';
import { IntlProvider } from 'react-intl';
import { fakeState } from 'Helpers';

export function css(jsx) {
  return (
    <div>
      <link type="text/css" rel="stylesheet" href="http://localhost:9666/pub/build/DeskPRO_AgentBundle_style.css" />
      {jsx}
    </div>
  );
}