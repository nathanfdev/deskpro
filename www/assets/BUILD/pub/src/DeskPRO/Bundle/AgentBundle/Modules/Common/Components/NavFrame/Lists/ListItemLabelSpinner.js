import React from 'react';
import Loader from '@deskpro/react-loader';

export class ListItemLabelSpinner extends React.Component {

  render() {
    return (
      <span>
        &nbsp;
        <Loader
          scale={0.35}
          left="22px"
          top="12px"
          color="green"
          width={3}
          component="span"
        />
      </span>
    );
  }
}
