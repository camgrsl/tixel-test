<script setup lang="ts">

const toast = useToast()

// Interfaces and enum declarations
enum OrderStatus {
  PLACED = 'placed',
  PREPARING = 'preparing',
  COOKING = 'cooking',
  READY_FOR_DELIVERY = 'ready_for_delivery'
}

interface Order {
  id: string,
  amount: string,
  created_at: string,
  updated_at: string,
  status: OrderStatus,
  transition: string,
}

// Fetch the orders from the api
const { data: orders } = await useAsyncData<{ data: Order[] }>(
    'orders',
    () => $fetch('http://localhost/orders')
)

// Update order with the API
const updateOrder = async (order: Order): Promise<void> => {
  try {
    const { data: updatedOrder } = await $fetch<{ data: Order }>( 'http://localhost/orders/' + order.id, {
      method: 'PATCH',
      body: {
        status: order.transition
      }
    } );

    // Find the index of the order in the current list
    if (orders.value?.data) {
      const index = orders.value.data.findIndex(
          (existingOrder) => existingOrder.id === updatedOrder.id
      );

      // If found, replace the order with the updated version
      if (index !== -1) {
        orders.value.data.splice(index, 1, updatedOrder);

        toast.add({ title: 'Order updated!' })
      }
    }
  } catch (error) {
    toast.add({ title: 'Error while updating order!' })
  }
}

// Decide ui color based on status
const getColor = (orderStatus: OrderStatus): string => {
  switch (orderStatus) {
    case OrderStatus.PLACED:
      return 'yellow'
    case OrderStatus.PREPARING:
      return 'indigo'
    case OrderStatus.COOKING:
      return 'orange'
    case OrderStatus.READY_FOR_DELIVERY:
      return 'green'
  }
}

// Format date
const formatDate = (date: string): string => {
  const parseDate = new Date(date)

  return parseDate.toUTCString()
}

</script>

<template>
  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
    <tr>
      <th scope="col" class="px-6 py-3">
        Order ID
      </th>
      <th scope="col" class="px-6 py-3">
        Amount
      </th>
      <th scope="col" class="px-6 py-3">
        Status
      </th>
      <th scope="col" class="px-6 py-3">
        Created At
      </th>
      <th scope="col" class="px-6 py-3">
        Updated At
      </th>
      <th scope="col" class="px-6 py-3">
        Action
      </th>
    </tr>
    </thead>
    <div v-if="orders === null">
      No data
    </div>
    <tbody v-else>
      <tr v-for="order in orders.data" :key="order.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
          {{ order.id }}
        </th>
        <td class="px-6 py-4">
          ${{ order.amount }}
        </td>
        <td class="px-6 py-4">
          <UBadge
            :color="getColor(order.status)"
            variant="solid"
          >
            {{ order.status }}
          </UBadge>
        </td>
        <td class="px-6 py-4">
          {{ formatDate(order.created_at) }}
        </td>
        <td class="px-6 py-4">
          {{ formatDate(order.updated_at) }}
        </td>
        <td class="px-6 py-4">
          <UButton
            v-if="order.transition !== null"
            @click="updateOrder(order)"
            :color="getColor(order.transition)"
            variant="outline"
            size="xs"
          >
            Transit to {{ order.transition }}
          </UButton>
        </td>
      </tr>
    </tbody>
  </table>
</template>