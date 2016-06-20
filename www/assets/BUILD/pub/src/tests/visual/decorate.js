import React from 'react';

export function decorate(jsx) {
  return (
    <div>
      <link type="text/css" rel="stylesheet" href="http://localhost:9666/pub/build/DeskPRO_AgentBundle_style.css" />
      {jsx}
    </div>
  );
}