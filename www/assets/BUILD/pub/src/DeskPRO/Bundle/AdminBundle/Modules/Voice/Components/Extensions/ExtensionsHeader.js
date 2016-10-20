import React from 'react';
import SectionHeader from '../../../Common/Components/SectionHeader';

class ExtensionsHeader extends React.Component {

  render() {
    return (
      <SectionHeader
        title="Extensions"
        description="Easily set up and manage extenions for all your agents as well as using targets for other extensions."
        dividing
      />
    );
  }
}

export default ExtensionsHeader;
