import React, { useState, useEffect } from 'react';
import { View, Text, Image, TouchableOpacity, TextInput, StyleSheet, ScrollView } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage'; // Import AsyncStorage
import BASE_URL from '../config/config';
import { launchImageLibrary } from 'react-native-image-picker';

const PlaceOrderPage = () => {
  const navigation = useNavigation();
  const [userId, setUserId] = useState<string | null>(null);
  const [cartItems, setCartItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [paymentMethod, setPaymentMethod] = useState<'walkin' | 'gcash'>('walkin');
  const [addresses, setAddresses] = useState([]);
  const [selectedAddress, setSelectedAddress] = useState(null);
  const [gcashScreenshot, setGcashScreenshot] = useState(null);
  const [referenceNumber, setReferenceNumber] = useState('');

  // Fetch user ID from AsyncStorage
  useEffect(() => {
    const getUserId = async () => {
      try {
        const storedUserId = await AsyncStorage.getItem('user_id');
        if (storedUserId) {
          setUserId(storedUserId);
        }
      } catch (error) {
        console.error('Error fetching user ID:', error);
      }
    };
    getUserId();
  }, []);

  // Fetch cart items
  useEffect(() => {
    if (!userId) return;
    
    const fetchCartItems = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/addtocart.php?user_id=${userId}`);
        if (response.data.success) {
          setCartItems(response.data.data);
        } else {
          setError(response.data.message);
        }
      } catch (error) {
        console.error('Error fetching cart items:', error);
        setError('Error fetching cart items');
      } finally {
        setLoading(false);
      }
    };

    fetchCartItems();
  }, [userId]);

  // Fetch all shipping addresses
  useEffect(() => {
    if (!userId) return;
    
    const fetchAddresses = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/getaddresses.php?user_id=${userId}`);
        if (response.data.addresses && response.data.addresses.length > 0) {
          const sortedAddresses = response.data.addresses.sort((a, b) => b.is_default - a.is_default);
          setAddresses(sortedAddresses);
        } else {
          setAddresses([]);
        }
      } catch (error) {
        console.error('Error fetching addresses:', error);
        setError('Error fetching shipping addresses');
      }
    };

    fetchAddresses();
  }, [userId]);

  const handlePaymentSelection = (method: 'walkin' | 'gcash') => {
    setPaymentMethod(method);
  };

  const handleConfirmOrder = async () => {
    const missingFields = [];
  
    // Check for missing required fields
    if (!selectedAddress) {
      missingFields.push('Shipping address');
    }
  
    if (paymentMethod === 'gcash') {
      if (!gcashScreenshot) {
        missingFields.push('Gcash payment screenshot');
      }
      if (!referenceNumber) {
        missingFields.push('Gcash reference number');
      }
    }
  
    // If there are missing fields, log them and alert the user
    if (missingFields.length > 0) {
      console.log('Missing fields:', missingFields);
      alert(`Please fill in the following missing fields: ${missingFields.join(', ')}`);
      return;
    }
  
    const orderData = {
      user_id: 1, // Replace with dynamic user_id if needed
      address_id: selectedAddress.address_id,
      payment_method: paymentMethod,
      cart_items: cartItems.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        total_price: item.total_price
      })),
      ...(paymentMethod === 'gcash' && {
        gcash_ref_number: referenceNumber,
        gcash_screenshot: gcashScreenshot?.uri, // Send the image URI (Consider uploading it to a server first)
      })
    };
  
    try {
      const response = await axios.post(`${BASE_URL}/placeorder.php`, orderData);
  
      if (response.data.success) {
        alert('Order placed successfully!');
        // Optionally clear cart or navigate
        setCartItems([]);
        setSelectedAddress(null);
        setPaymentMethod('walkin');
        setGcashScreenshot(null);
        setReferenceNumber('');
      } else {
        alert(`Order failed: ${response.data.message}`);
      }
    } catch (error) {
      console.error('Error placing order:', error);
      alert('An error occurred while placing your order.');
    }
  };  
  
  
  

  const handleAddShippingAddress = () => {
    navigation.navigate('AddShippingAddressPage');
  };

  const handleSelectAddress = (address) => {
    // Set the selected address to the one clicked
    setSelectedAddress(address);
  };

  // Function to launch the image picker
  const handleUploadScreenshot = () => {
    launchImageLibrary({ mediaType: 'photo', includeBase64: false }, (response) => {
      if (response.didCancel) {
        console.log('User cancelled image picker');
      } else if (response.errorCode) {
        console.error('Image Picker Error:', response.errorMessage);
      } else {
        const uri = response.assets[0].uri;
        const filename = uri.split('/').pop(); // Extract the filename from the URI
        setGcashScreenshot({ uri, filename }); // Store both URI and filename
      }
    });
  };


  return (
    <View style={styles.container}>
      <ScrollView contentContainerStyle={styles.scrollViewContent}>
        {/* Shipping Address Section */}
        <View style={styles.section}>
          <Text style={styles.headerText}>Shipping Address</Text>
          <View style={styles.sectionUnderline} />
          {addresses.length > 0 ? (
            addresses.map((address) => (
              <TouchableOpacity
                key={address.address_id}
                style={[
                  styles.addressContainer,
                  selectedAddress?.address_id === address.address_id && styles.selectedAddress, // Apply selected styles
                ]}
                onPress={() => handleSelectAddress(address)}
              >
                <Text style={styles.addressName}>{address.recipient_name}</Text>
                <Text style={styles.addressText}>
                  {address.street_address}, {address.barangay}, {address.city}, {address.postal_code}
                </Text>
                <Text style={styles.addressPhone}>{`0${address.mobile_number}`}</Text>
                {selectedAddress?.address_id === address.address_id && (
                  <Text style={styles.selectedText}>Selected</Text>
                )}
              </TouchableOpacity>
            ))
          ) : (
            <Text>No addresses available. Add one below.</Text>
          )}
        </View>

        {/* Always display Add Shipping Address button */}
        <TouchableOpacity style={styles.addAddressButton} onPress={handleAddShippingAddress}>
          <Text style={styles.addAddressText}>Add Shipping Address</Text>
        </TouchableOpacity>

        {/* Product Info Section */}
        <View style={styles.section}>
          <Text style={styles.headerText}>Product Info</Text>
          <View style={styles.sectionUnderline} />
          {loading ? (
            <Text>Loading...</Text>
          ) : error ? (
            <Text>{error}</Text>
          ) : cartItems.length > 0 ? (
            cartItems.map((item, index) => (
              <View key={index} style={styles.productRow}>
                <Image source={{ uri: `${BASE_URL}/${item.image_url}` }} style={styles.productImageSmall} />
                <View style={styles.productDetails}>
                  <Text style={styles.productName}>
                    {item.name} - <Text style={styles.variantName}>{item.variant_name}</Text>
                  </Text>
                  <Text style={styles.quantityText}>Quantity: {item.quantity}</Text>
                  <Text style={styles.totalPriceText}>Total: ₱{item.total_price.toFixed(2)}</Text>
                </View>
              </View>
            ))
          ) : (
            <Text>No items in cart.</Text>
          )}
        </View>

        {/* Payment Method Section */}
        <View style={styles.section}>
          <Text style={styles.headerText}>Select Payment Method</Text>
          <View style={styles.sectionUnderline} />
          <TouchableOpacity
            style={[styles.paymentButton, paymentMethod === 'walkin' && styles.selectedPayment]}
            onPress={() => handlePaymentSelection('walkin')}
          >
            <Text style={styles.paymentText}>Walk In</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.paymentButton, paymentMethod === 'gcash' && styles.selectedPayment]}
            onPress={() => handlePaymentSelection('gcash')}
          >
            <Text style={styles.paymentText}>Gcash</Text>
          </TouchableOpacity>
        </View>

        {/* Gcash Payment Details (Only shown if Gcash is selected) */}
        {paymentMethod === 'gcash' && (
          <View style={styles.gcashPaymentDetails}>
            <Text style={styles.headerText}>Upload Gcash Payment Screenshot</Text>
            <TouchableOpacity onPress={handleUploadScreenshot} style={styles.uploadButton}>
              <Text style={styles.uploadButtonText}>Select Screenshot</Text>
            </TouchableOpacity>

            {gcashScreenshot && (
              <>
                <Image source={{ uri: gcashScreenshot.uri }} style={styles.screenshotPreview} />
                <Text style={styles.filenameText}>File: {gcashScreenshot.filename}</Text>
              </>
            )}

            <Text style={styles.headerText}>Reference Number</Text>
            <TextInput
              style={styles.input}
              placeholder="Enter Reference Number"
              placeholderTextColor="#000"
              value={referenceNumber}
              keyboardType="numeric"
              onChangeText={(text) => setReferenceNumber(text.replace(/[^0-9]/g, ''))} // Allow only numbers
            />
          </View>
        )}

        {/* Total Price Section */}
        <View style={styles.section}>
          <Text style={styles.headerText}>Total Price</Text>
          <View style={styles.sectionUnderline} />
          <Text style={styles.totalPriceText}>
            Order Total: ₱
            {(cartItems.reduce((total, item) => total + item.total_price, 0)).toFixed(2)}
          </Text>
        </View>
      </ScrollView>

      {/* Confirm Order Button */}
      <TouchableOpacity style={styles.orderButton} onPress={handleConfirmOrder}>
        <Text style={styles.orderButtonText}>Confirm Order</Text>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f4f4f4' },
  scrollViewContent: { padding: 16, paddingBottom: 80 },
  section: { marginBottom: 16, },
  headerText: { fontSize: 20, fontWeight: 'bold', color: '#333', marginBottom: 8 },
  
  // Underline style for each section
  sectionUnderline: {
    width: '100%',
    height: 1,
    backgroundColor: '#ccc',
    marginBottom: 16, // Space between the underline and the content
  },

  card: { backgroundColor: '#fff', borderRadius: 10, padding: 16, marginVertical: 8, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.3, shadowRadius: 4, elevation: 5 },
  productImage: { width: '100%', height: 200, borderRadius: 10, resizeMode: 'cover', marginBottom: 8 },
  paymentButton: { backgroundColor: '#f9f9f9', padding: 12, borderRadius: 5, marginBottom: 8, alignItems: 'center', borderWidth: 1, borderColor: '#ddd' },
  selectedPayment: { borderColor: '#4CAF50', backgroundColor: '#e8f5e9' },
  paymentText: { fontSize: 16, color: '#333' },
  orderButton: { backgroundColor: '#4CAF50', paddingVertical: 14, borderRadius: 8, alignItems: 'center', marginTop: 'auto', margin: 16, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.3, shadowRadius: 4, elevation: 5 },
  orderButtonText: { fontSize: 18, color: '#fff', fontWeight: 'bold' },
  addressName: { fontSize: 18, fontWeight: 'bold', color: '#000' },
  addressText: { fontSize: 14, color: '#555' },
  addressPhone: { fontSize: 14, color: '#555', marginTop: 4 },
  selectedText: { fontSize: 14, color: '#4CAF50', marginTop: 8 },
  addAddressButton: { backgroundColor: '#ddd', padding: 12, borderRadius: 8, alignItems: 'center', marginBottom: 40, },
  addAddressText: { fontSize: 16, color: '#000', },

  addressContainer: {
    backgroundColor: '#fff',
    padding: 12,
    borderRadius: 5,
    marginBottom: 8,
    width: '100%',
    borderWidth: 1,
    borderColor: '#ddd', // Default border color
  },
  selectedAddress: {
    borderColor: '#4CAF50', // Green border when selected
    borderWidth: 2, // Thicker border for emphasis
  },

  productRow: {
    flexDirection: 'row', // Horizontal layout
    alignItems: 'center',
    backgroundColor: '#fff',
    padding: 12,
    borderRadius: 5,
    marginBottom: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 5,
  },
  productImageSmall: {
    width: 60, // Small image size
    height: 60,
    borderRadius: 8,
    marginRight: 12, // Spacing between image and text
  },
  productDetails: {
    flex: 1, // Take remaining space
  },
  productName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  variantName: {
    fontSize: 14,
    color: '#555',
  },
  quantityText: {
    fontSize: 14,
    color: '#666',
    marginTop: 4,
  },
  totalPriceText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#4CAF50',
    marginTop: 4,
  },

  uploadButton: {
    backgroundColor: '#4CAF50',
    padding: 12,
    borderRadius: 5,
    alignItems: 'center',
    marginBottom: 16,
  },
  uploadButtonText: {
    color: '#fff',
    fontSize: 16,
  },

  screenshotPreview: {
    width: 100,
    height: 100,
    resizeMode: 'contain',
    marginTop: 8,
  },

  gcashPaymentDetails: {
    marginTop: 16,
  },

  input: {
    height: 40,
    borderColor: '#ddd',
    borderWidth: 1,
    borderRadius: 5,
    marginBottom: 16,
    paddingLeft: 8,
    fontSize: 16,
    color: 'black',
  },

  filenameText: {
    fontSize: 14,
    color: '#333',
    marginTop: 8,
  },  
});

export default PlaceOrderPage;
